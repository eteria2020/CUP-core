<?php

namespace SharengoCore\Utils;

final class Interval
{
    /**
     * @var DateTime $start
     */
    private $start;

    /**
     * @var DateTime $end
     */
    private $end;

    public function __construct(\DateTime $start, \DateTime $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    public function seconds()
    {
        return $this->end->getTimestamp() - $this->start->getTimestamp();
    }

    public function minutes()
    {
        return ceil($this->seconds() / 60);
    }

    /**
     * @return DateTimeInterface
     */
    public function start()
    {
        return $this->start;
    }

    /**
     * @return DateTimeInterface
     */
    public function end()
    {
        return $this->end;
    }

    public function contains(\DateTime $date)
    {
        return $this->start <= $date && $this->end >= $date;
    }

    public function strictlyContains(\DateTime $date)
    {
        return $this->start < $date && $this->end > $date;
    }

    public function intersection(Interval $interval)
    {
        $start = max($this->start, $interval->start());
        $end = min($this->end, $interval->end());

        if ($start <= $end) {
            return new Interval($start, $end);
        }
    }

    /**
     * returns an array with the range of years in which the interval spreads
     *
     * @return array of integers
     */
    public function years()
    {
        return range($this->start->format('Y'), $this->end->format('Y'));
    }

    /**
     * returns an array with the range of days in which the interval spreads
     *
     * @return \DatePeriod
     */
    public function days()
    {
        $start = clone $this->start;
        $end = clone $this->end;

        $interval = new \DateInterval('P1D');
        return new \DatePeriod($start->modify('00:00:00'), $interval, $end->modify('23:59:59'));
    }
}
