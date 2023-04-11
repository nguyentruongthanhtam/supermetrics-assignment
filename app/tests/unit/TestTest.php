<?php

declare(strict_types=1);

namespace Tests\unit;

use ArrayIterator;
use DateTime;
use PHPUnit\Framework\TestCase;
use SocialPost\Hydrator\FictionalPostHydrator;
use Statistics\Builder\ParamsBuilder;
use Statistics\Calculator\Factory\StatisticsCalculatorFactory;
use Statistics\Dto\StatisticsTo;
use Statistics\Enum\StatsEnum;
use Statistics\Service\StatisticsService;

/**
 * Class ATestTest
 *
 * @package Tests\unit
 */
class TestTest extends TestCase
{
    /**
     * @test
     */

    private ArrayIterator $postsTraversable;
    private StatisticsService $statisticsService;

    protected function setUp(): void
    {
        // Decode the JSON file
        $mockJSON = file_get_contents(__DIR__ . '/../data/social-posts-response.json', true);
        $json_data = json_decode($mockJSON, true);
        $mockPosts = ($json_data["data"]["posts"]);
        $socialPostsArray = array();
        $hydrator = new FictionalPostHydrator();
        foreach ($mockPosts as $post) {
            $socialPostsArray[] = $hydrator->hydrate($post);;
        }
        $this->postsTraversable = new ArrayIterator($socialPostsArray);
        $statisticsFactory = new StatisticsCalculatorFactory();
        $this->statisticsService = new StatisticsService($statisticsFactory);
    }

    public function testAveragePostsPerUserPerMonth(): void
    {
        // Create Params
        $startDate = DateTime::createFromFormat('F, Y', "July, 2018");
        $endDate = DateTime::createFromFormat('F, Y', "October, 2018");
        $params = ParamsBuilder::reportStatsParams($startDate, $endDate);
        $result = $this->statisticsService->calculateStats($this->postsTraversable, $params);
        // Start Testing
        $expectedAveragePost = 1;
        $expectedFunctionName = StatsEnum::AVERAGE_POSTS_NUMBER_PER_USER_PER_MONTH;
        $calculatedAveragePost = 0;
        foreach ($result->getChildren() as $section) {
            if ($section->getName() == StatsEnum::AVERAGE_POSTS_NUMBER_PER_USER_PER_MONTH) {
                $calculatedAveragePost = $section;
            }
        };

        $this->assertTrue($expectedAveragePost == $calculatedAveragePost->getValue());
        $this->assertTrue($expectedFunctionName == $calculatedAveragePost->getName());
    }

    public function testMaxCharacterLength(): void
    {
        // Create Params
        $startDate = DateTime::createFromFormat('F, Y', "July, 2018");
        $endDate = DateTime::createFromFormat('F, Y', "October, 2018");
        $params = ParamsBuilder::reportStatsParams($startDate, $endDate);
        $result = $this->statisticsService->calculateStats($this->postsTraversable, $params);
        // Start Testing
        $expectedLength = 495.25;
        $calculatedPost = new  StatisticsTo();
        $expectedFunctionName = StatsEnum::AVERAGE_POST_LENGTH;
        foreach ($result->getChildren() as $section) {
            if ($section->getName() == StatsEnum::AVERAGE_POST_LENGTH) {
                $calculatedPost = $section;
            }
        };
        $this->assertTrue((float)$expectedLength == $calculatedPost->getValue(), "No pass");
        $this->assertTrue($expectedFunctionName == $calculatedPost->getName());
    }
}
