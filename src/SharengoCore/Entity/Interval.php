<?php

namespace SharengoCore\Entity;

final class Interval
{
    /**
     * @var DateTimeInterface $start
     */
    private $start;

    /**
     * @var DateTimeInterface $end
     */
    private $end;

    public function __construct(\DateTimeInterface $start, \DateTimeInterface $end)
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

    public function contains(DateTimeInterface $date)
    {
        return $this->start <= $date && $this->end <= $date;
    }
}
