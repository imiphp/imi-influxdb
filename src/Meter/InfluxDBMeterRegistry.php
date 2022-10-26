<?php

declare(strict_types=1);

namespace Imi\InfluxDB\Meter;

use Imi\InfluxDB\InfluxDB;
use Imi\Meter\Contract\BaseMeterRegistry;
use Imi\Meter\Contract\ICounter;
use Imi\Meter\Contract\IGauge;
use Imi\Meter\Contract\IHistogram;
use Imi\Meter\Contract\IMeter;
use Imi\Meter\Contract\ISummary;
use Imi\Meter\Contract\ITimer;
use Imi\Meter\Traits\TPushMeterRegistry;
use InfluxDB\Point;

if (class_exists(BaseMeterRegistry::class))
{
    class InfluxDBMeterRegistry extends BaseMeterRegistry
    {
        use TPushMeterRegistry;

        public function __construct(array $config = [])
        {
            parent::__construct($config);
            $this->start();
        }

        public function publish(): void
        {
            $config = $this->getConfig();
            if (!isset($config['database']))
            {
                throw new \InvalidArgumentException('Config @app.beans.MeterRegistry.options.database required');
            }
            $database = $config['database'];
            $clientName = $config['clientName'] ?? null;
            $batch = $config['batch'] ?? 1000;
            $points = [];
            $count = 0;
            $database = InfluxDB::getDatabase($database, $clientName);
            foreach ($this->getMeters() as $meter)
            {
                switch (true)
                {
                    case $meter instanceof ICounter:
                        $points[] = $this->buildCounterPoint($meter);
                        break;
                    case $meter instanceof IGauge:
                        $points[] = $this->buildGaugePoint($meter);
                        break;
                    case $meter instanceof IHistogram:
                        $points[] = $this->buildHistogramPoint($meter);
                        break;
                    case $meter instanceof ISummary:
                        $points[] = $this->buildDistributionSummaryPoint($meter);
                        break;
                    case $meter instanceof ITimer:
                        $points[] = $this->buildTimerPoint($meter);
                        break;
                    default:
                        throw new \RuntimeException(sprintf('Unknown meter type %s', $meter->getType()));
                }
                ++$count;
                if ($count >= $batch)
                {
                    $database->writePoints($points);
                    $points = [];
                    $count = 0;
                }
            }
            if ($points)
            {
                $database->writePoints($points);
            }
        }

        private function buildCounterPoint(ICounter $counter): Point
        {
            $tags = $counter->getTags();
            $this->parseTags($tags, $counter);

            return new Point($counter->getName(), $counter->value(), $tags);
        }

        private function buildGaugePoint(IGauge $gauge): Point
        {
            $tags = $gauge->getTags();
            $this->parseTags($tags, $gauge);

            return new Point($gauge->getName(), $gauge->value(), $tags);
        }

        private function buildHistogramPoint(IHistogram $histogram): Point
        {
            $tags = $histogram->getTags();
            $this->parseTags($tags, $histogram);

            return new Point($histogram->getName(), null, $tags, [
                'sum'   => $histogram->totalAmount(),
                'count' => $histogram->count(),
                'mean'  => $histogram->mean(),
            ]);
        }

        private function buildDistributionSummaryPoint(ISummary $summary): Point
        {
            $tags = $summary->getTags();
            $this->parseTags($tags, $summary);

            return new Point($summary->getName(), null, $tags, [
                'sum'   => $summary->totalAmount(),
                'count' => $summary->count(),
                'mean'  => $summary->mean(),
            ]);
        }

        private function buildTimerPoint(ITimer $timer): Point
        {
            $tags = $timer->getTags();
            $this->parseTags($tags, $timer);

            return new Point($timer->getName(), null, $tags, [
                'sum'   => $timer->totalAmount(),
                'count' => $timer->count(),
                'mean'  => $timer->mean(),
            ]);
        }

        private function parseTags(array &$tags, IMeter $meter): void
        {
            $tags['metric_type'] = $meter->getType();
        }
    }
}
