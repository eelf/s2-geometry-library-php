<?php

class SmokeTest extends PHPUnit_Framework_TestCase
{

    const EARTH_RADIUS = 6371000;

    /**
     * @param \S2\S2LatLng $a
     * @param \S2\S2LatLng $b
     * @return double Distance in meters
     */
    public static function GreatEarthDistance(\S2\S2LatLng $a, \S2\S2LatLng $b)
    {
        $angle = self::Haversin($a->latRadians() - $b->latRadians())
            + cos($a->latRadians()) * cos($b->latRadians()) * self::Haversin($a->lngRadians() - $b->lngRadians());
        $ret = 2 * self::EARTH_RADIUS * asin(sqrt($angle));
        return $ret;
    }

    public static function Haversin($a)
    {
        return (1 - cos($a)) / 2;
    }

    public static function greatCircleBearing(\S2\S2LatLng $a, \S2\S2LatLng $b)
    {
        $cos_latb = cos($b->latRadians());
        $dlon = $b->lngRadians() - $a->lngRadians();
        $y = sin($dlon) * $cos_latb;
        $x = cos($a->latRadians()) * sin($b->latRadians()) - sin($a->latRadians()) * $cos_latb * cos($dlon);
        $brng = atan2($y, $x);
        return $brng;
    }

    public static function greatCircleDestination(\S2\S2LatLng $a, $bearing, $distance)
    {
        $cos_dist_earth = cos($distance / self::EARTH_RADIUS);
        $sin_dist_earth = sin($distance / self::EARTH_RADIUS);
        $sin_lat = sin($a->latRadians());
        $cos_lat = cos($a->latRadians());
        $lat = asin(
            $sin_lat * $cos_dist_earth +
            $cos_lat * $sin_dist_earth * cos($bearing)
        );
        $lng = $a->lngRadians()
            + atan2(
                sin($bearing) * $sin_dist_earth * $cos_lat,
                $cos_dist_earth - $sin_lat * sin($lat)
            );
        return new \S2\S2LatLng($lat, $lng);
    }

    public static function encodeLocation(\S2\S2LatLng $s2ll)
    {
        $lat = (int)($s2ll->latDegrees() * 1000000);
        $lng = (int)($s2ll->lngDegrees() * 1000000);
        $ret = sprintf("%08x,%08x", $lat, $lng);
        return $ret;
    }

    public static function decodeLocation($loc)
    {
        if (strpos($loc, ',') === false) return false;
        list ($lat, $lng) = explode(',', $loc);
        return \S2\S2LatLng::fromDegrees(hexdec($lat) / 1000000, hexdec($lng) / 1000000);
    }

    function find_some_point_with_distance($lat, $lng)
    {
        // find some point with distance in 30..40 from specified point
        $lat_p = array($lat, $lat, 0, $lat + 0.01, 1e10);
        $lng_p = array($lng, $lng, 0, $lng + 0.01, 1e10);
        $loc_port = \S2\S2LatLng::fromDegrees($lat, $lng);
        for ($i = 0; $i < 10; $i++) {
            $loc_1 = \S2\S2LatLng::fromDegrees($lat_p[0], $lng_p[0]);
            $dist = self::GreatEarthDistance($loc_1, $loc_port);
            if ($dist < 30) {
                if ($dist > $lat_p[2]) {
                    $lat_p[2] = $dist;
                    $lat_p[1] = $lat_p[0];
                }
                if ($dist > $lng_p[2]) {
                    $lng_p[2] = $dist;
                    $lng_p[1] = $lng_p[0];
                }
            } else if ($dist > 39.9) {
                if ($dist < $lat_p[4]) {
                    $lat_p[4] = $dist;
                    $lat_p[3] = $lat_p[0];
                }
                if ($dist < $lng_p[4]) {
                    $lng_p[4] = $dist;
                    $lng_p[3] = $lng_p[0];
                }
            } else {
                echo "found $lat_p[0] $lng_p[0]\n";
                break;
            }
            $lat_p[0] = $lat_p[1] + ($lat_p[3] - $lat_p[1]) / 2;
            $lng_p[0] = $lng_p[1] + ($lng_p[3] - $lng_p[1]) / 2;
        }
        die;
    }

    private static function guidToToken($guid)
    {
        return substr($guid, 0, 16);
    }

    public function testA()
    {
        $hex_loc = '0351272d,0242b406';
        $this->assertEquals($hex_loc, self::encodeLocation(self::decodeLocation($hex_loc)));

        $from = \S2\S2LatLng::fromDegrees(55.578201, 37.912176);
        $to = \S2\S2LatLng::fromDegrees(55.578324, 37.9109);

        $dist = self::GreatEarthDistance($from, $to);

        $bearing = self::greatCircleBearing($from, $to);
        $to2 = self::greatCircleDestination($from, $bearing, 40);
        $bearing2 = self::greatCircleBearing($to2, $to);
        $to3 = self::greatCircleDestination($to2, $bearing2, $dist - 40);

        $dist3 = self::GreatEarthDistance($from, $to3);

        $this->assertEquals(0.9700225997852, $from->latRadians());
        $this->assertEquals(0.66169229779557, $from->lngRadians());

        $this->assertEquals(0.97002474654019, $to->latRadians());
        $this->assertEquals(0.66167002739432, $to->lngRadians());

        $this->assertEquals(0.97002365521829, $to2->latRadians());
        $this->assertEquals(0.66168134906715, $to2->lngRadians());

        $this->assertEquals(0.97002474654019, $to3->latRadians());
        $this->assertEquals(0.66167002739432, $to3->lngRadians());

        $this->assertEquals(-1.4018857232359, $bearing);
        $this->assertEquals(-1.4018947548004, $bearing2);
        $this->assertEquals(81.362381188294, $dist);
        $this->assertEquals(81.362381188297, $dist3);
    }

    public function testB()
    {
        $lat = 55.613855;
        $lng = 37.978578;

        $loc = \S2\S2LatLng::fromDegrees(55.6141284375, 37.9788514375);

        $s2ll = \S2\S2CellId::fromToken(self::guidToToken('700c7c5346a246ee88eee70b200d0b33.16'))->toLatLng();
        $this->assertEquals('(-0.023004811178492, -3.06557268979)', (string)$s2ll);

        $s2ll = \S2\S2CellId::fromToken(self::guidToToken('414ab9b68fd0000000082a7300000025.6'))->toLatLng();
        $this->assertEquals('(0.97053474915648, 0.66268771618818)', (string)$s2ll);

        $loc_1 = \S2\S2LatLng::fromDegrees(55.605873, 37.970864);
        $loc_ezhio = \S2\S2LatLng::fromE6(55608152, 37972176);
        $loc_oktz = \S2\S2LatLng::fromE6(55607195, 37971367);
        $loc_art = \S2\S2LatLng::fromE6(55605726, 37970664);
        $dist = self::GreatEarthDistance($loc_1, $s2ll);
        $this->assertEquals(212.99711509717, $dist);
    }

    public function testPolygon()
    {
        $S2Polygon = new \S2\S2Polygon();

        $S2Polygon = new \S2\S2Polygon();
    }
}
