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
        return floor($this->seconds() / 60);
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
        return $this->start <= $date && $this->end <= $date;
    }
}
