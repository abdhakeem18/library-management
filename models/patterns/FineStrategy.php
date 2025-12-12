<?php

// Strategy Pattern for Fine Calculation

interface FineCalculationStrategy
{
    public function calculateFine($daysOverdue);
    public function getDueDateDays();
    public function getFineRate();
}

class StudentFineStrategy implements FineCalculationStrategy
{
    public function calculateFine($daysOverdue)
    {
        return $daysOverdue * 50; // LKR 50/day
    }

    public function getDueDateDays()
    {
        return 14; // 14 days for students
    }

    public function getFineRate()
    {
        return 50;
    }
}

class FacultyFineStrategy implements FineCalculationStrategy
{
    public function calculateFine($daysOverdue)
    {
        return $daysOverdue * 20; // LKR 20/day
    }

    public function getDueDateDays()
    {
        return 30; // 30 days for faculty
    }

    public function getFineRate()
    {
        return 20;
    }
}

class GuestFineStrategy implements FineCalculationStrategy
{
    public function calculateFine($daysOverdue)
    {
        return $daysOverdue * 100; // LKR 100/day
    }

    public function getDueDateDays()
    {
        return 7; // 7 days for guests
    }

    public function getFineRate()
    {
        return 100;
    }
}

class FineContext
{
    private $strategy;

    public function setStrategy(FineCalculationStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    public function calculateFine($daysOverdue)
    {
        if (!$this->strategy) {
            throw new Exception("Fine calculation strategy not set");
        }
        return $this->strategy->calculateFine($daysOverdue);
    }

    public function getDueDateDays()
    {
        if (!$this->strategy) {
            throw new Exception("Fine calculation strategy not set");
        }
        return $this->strategy->getDueDateDays();
    }

    public function getFineRate()
    {
        if (!$this->strategy) {
            throw new Exception("Fine calculation strategy not set");
        }
        return $this->strategy->getFineRate();
    }
}
