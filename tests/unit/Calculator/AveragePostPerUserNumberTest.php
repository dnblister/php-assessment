<?php

declare(strict_types = 1);

namespace Tests\unit\Calculator;

use PHPUnit\Framework\TestCase;
use SocialPost\Hydrator\FictionalPostHydrator;
use Statistics\Builder\ParamsBuilder;
use Statistics\Calculator\AveragePostPerUserNumber;
use Statistics\Dto\ParamsTo;
use Statistics\Enum\StatsEnum;

/**
 * Class ATestTest
 *
 * @package Tests\unit
 */
class AveragePostPerUserNumberTest extends TestCase
{
    private const TEST_DATE = '2018-08-10';

    private ParamsTo $paramsTo;

    protected function setUp(): void
    {
        foreach (ParamsBuilder::reportStatsParams(\DateTime::createFromFormat('Y-m-d', self::TEST_DATE)) as $paramsTo) {
            if ($paramsTo-> getStatName() === StatsEnum::AVERAGE_POST_NUMBER_PER_USER) {
                $this->paramsTo = $paramsTo;
            }
        }
    }

    /**
     * @covers AveragePostPerUserNumber::calculate()
     */
    public function testStatisticsReturnZeroIfNoOnePostWasPassed(): void
    {
        $calculator = (new AveragePostPerUserNumber())->setParameters($this->paramsTo);
        $statisticsTo = $calculator->calculate();

        $this->assertEquals(0, $statisticsTo->getValue());
    }

    /**
     * @dataProvider providePosts
     */
    public function testStatisticsReturnNonZeroForPostsWithAcceptableDate(array $posts): void
    {
        $calculator = (new AveragePostPerUserNumber())->setParameters($this->paramsTo);
        foreach ($posts as $post) {
            $calculator->accumulateData($post);
        }

        $statisticsTo = $calculator->calculate();

        $this->assertEquals(1, $statisticsTo->getValue());
    }

    public function providePosts(): array
    {
        return [
            [
                $this->prepareData(),
            ],
        ];
    }

    private function prepareData(): array
    {
        $dataPath = __DIR__ . '/../../data/social-posts-response.json';
        $rawData = file_get_contents($dataPath);
        if (false === $rawData) {
            throw new \InvalidArgumentException("File at path `{$dataPath}` doesn't exists");
        }

        $hydrator = new FictionalPostHydrator();
        $decodedData = json_decode($rawData, true);

        $data = [];
        foreach ($decodedData['data']['posts'] as $item) {
            $data[] = $hydrator->hydrate($item);
        }

        return $data;
    }
}
