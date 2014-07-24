<?php

namespace Sulu\Bundle\LocationBundle\Tests\Unit\Geolocator\Service;

use Sulu\Bundle\LocationBundle\Geolocator\GeolocatorManager;
use Guzzle\Http\Client;
use Sulu\Bundle\LocationBundle\Geolocator\Service\GoogleGeolocator;
use Guzzle\Plugin\Mock\MockPlugin;
use Guzzle\Http\Message\Response;

class GoogleGeolocatorTest extends \PHPUnit_Framework_TestCase
{
    protected $geolocator;
    protected $mockPlugin;

    public function setUp()
    {
        $client = new Client();
        $this->mockPlugin = new MockPlugin();
        $client->addSubscriber($this->mockPlugin);

        $this->geolocator = new GoogleGeolocator($client);
    }

    public function provideLocate()
    {
        return array(
            array(
                '10, Rue Alexandre Dumas, Paris',
                1,
                array(
                    'displayTitle' => '10 Rue Alexandre Dumas, 75011 Paris, France',
                    'street' => 'Rue Alexandre Dumas',
                    'number' => '10',
                    'code' => '75011',
                    'town' => 'Paris',
                    'country' => 'France',
                    'longitude' => '2.3897064000000001',
                    'latitude' => '48.852964900000003',
                )
            ),
            array(
                'Dornbirn',
                1,
                array(
                    'displayTitle' => 'Dornbirn, Austria',
                    'street' => null,
                    'number' => null,
                    'code' => null,
                    'town' => 'Dornbirn',
                    'country' => 'Austria',
                    'longitude' => '9.7437899999999988',
                    'latitude' => '47.412399999999998',
                )
            )
        );
    }

    /**
     * @dataProvider provideLocate
     */
    public function testLocate($query, $expectedCount, $expectationMap)
    {
        $fixtureName = __DIR__ . '/google-responses/' . md5($query).'.json';
        $fixture = file_get_contents($fixtureName);
        $this->mockPlugin->addResponse(new Response(200, null, $fixture));

        $results = $this->geolocator->locate($query);
        $this->assertCount($expectedCount, $results);

        if (0 == count($results)) {
            return;
        }

        $result = current($results->toArray());

        foreach ($expectationMap as $field => $expectation) {
            $this->assertEquals($expectation, $result[$field]);
        }
    }
}

