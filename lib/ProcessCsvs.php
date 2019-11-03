<?php
class ProcessCsvs
{
    /**
     * @var \MongoCollection
     */
    private $mongo;


    /**
     * @var string
     */
    private $path = null;

    /**
     *
     * @param string $path
     * @param \MongoCollection $mongo
     * @throws \Exception
     */
    public function __construct($path, $mongo)
    {
        if (!is_dir($path)) {
            throw new \Exception('Path "' . $path . '" is not a directory');
        }
        $this->path = $path;
        $this->mongo = $mongo;
        $this->mongo->ensureIndex(array('raw_postcode' => 1), array('background' => true, 'unique' => true));
        $this->mongo->ensureIndex(array('pos' => "2d"), array('background' => true));
    }

    public function run()
    {
        $files = scandir($this->path);
        foreach ($files as $file) {
            if (substr($file, -4, 4) !== '.csv') {
                continue;
            }
            $path = $this->path . '/' . $file;
            echo "Processing: " . $path . "\n";
            $this->processFile($path);
            echo "Finished Processing: " . $path . "\n";
        }
    }

    private function processFile($file)
    {
        $row = 1;
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row++;
                $this->oneRow($data);
            }
            fclose($handle);
        }
    }

    private function oneRow($row)
    {
        if (count($row) != 10) {
            return;
        }
        if (!is_numeric($row[1]) || !is_numeric($row[2]) || !is_numeric($row[3])) {
            return;
        }
        // Postcode    Positional_quality_indicator    Eastings    Northings    Country_code    NHS_regional_HA_code    NHS_HA_code    Admin_county_code    Admin_district_code    Admin_ward_code
        $postCode = strtoupper($row[0]);
        $sanitisedPostCode = str_replace(' ', '', $postCode);
        $quality = (int) $row[1];
        $northings = (int) $row[3];
        $eastings = (int) $row[2];

        $inst = new NorthingsEastingsToCoordinates($eastings, $northings);
        $geo = $inst->Convert();
        $latitude = $geo['latitude'];
        $longitude = $geo['longitude'];
        $this->mongo->insert(
                array(
                        'country' => 'UK',
                        'raw_postcode' => $postCode,
                        'clean_postcode' => $sanitisedPostCode,
                        'northing' => $northings,
                        'easting' => $eastings,
                        'pos' => array('lng' => $longitude, 'lat' => $latitude)
                )
        );
    }
}
