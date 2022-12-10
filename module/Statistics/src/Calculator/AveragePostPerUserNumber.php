<?php

declare(strict_types=1);

namespace Statistics\Calculator;

use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

class AveragePostPerUserNumber extends AbstractCalculator
{
    protected const UNITS = 'posts';

    private array $posts = [];

    protected function doAccumulate(SocialPostTo $postTo): void
    {
        if (!array_key_exists($postTo->getAuthorId(), $this->posts)) {
            $this->posts[$postTo->getAuthorId()] = 1;
        } else {
            $this->posts[$postTo->getAuthorId()] += 1;
        }
    }

    protected function doCalculate(): StatisticsTo
    {
        $average = !empty($this->posts)
            ? array_sum($this->posts)/count($this->posts)
            : 0
        ;

        return (new StatisticsTo())
            ->setName($this->parameters->getStatName())
            ->setValue(round($average))
            ->setUnits(self::UNITS)
        ;
    }
}