<?php

declare(strict_types=1);

namespace Statistics\Calculator;

use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

class NoopCalculator extends AbstractCalculator
{
    protected const UNITS = 'posts';

    /**
     * @var array
     */
    private $totals = [];
    private $totalsAuthors = [];
    private $totalPosts = 0;

    /**
     * @inheritDoc
     */
    protected function doAccumulate(SocialPostTo $postTo): void
    {
        $key = $postTo->getDate()->format('M, Y');
        $this->totals[$key] = ($this->totals[$key] ?? 0) + 1;
        if (empty($this->totalsAuthors) || !in_array($postTo->getAuthorId(), $this->totalsAuthors)) {
            $this->totalsAuthors[] = $postTo->getAuthorId();
        }
    }

    /**
     * @inheritDoc
     */
    protected function doCalculate(): StatisticsTo
    {
        $stats = new StatisticsTo();
        foreach ($this->totals as $splitPeriod => $total) {
            $this->totalPosts += $total;
        }
        if(sizeof($this->totalsAuthors) <= 0 || sizeof($this->totals) <= 0) {
            return (new StatisticsTo())->setValue(0);
        }
        $averagePUPM = $this->totalPosts / sizeof($this->totalsAuthors) / sizeof($this->totals);
        return (new StatisticsTo())->setValue(round($averagePUPM,1));
    }
}
