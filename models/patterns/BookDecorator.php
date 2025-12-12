<?php

// Decorator Pattern for Book Features

interface BookInterface
{
    public function getTitle();
    public function getDescription();
    public function getPrice();
    public function getFeatures();
}

class BaseBook implements BookInterface
{
    protected $title;
    protected $description;
    protected $price;
    protected $bookData;

    public function __construct($bookData)
    {
        $this->bookData = $bookData;
        $this->title = $bookData['title'] ?? '';
        $this->description = $bookData['description'] ?? '';
        $this->price = $bookData['price'] ?? 0;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getFeatures()
    {
        return [];
    }

    public function getBookData()
    {
        return $this->bookData;
    }
}

abstract class BookDecorator implements BookInterface
{
    protected $book;

    public function __construct(BookInterface $book)
    {
        $this->book = $book;
    }

    public function getTitle()
    {
        return $this->book->getTitle();
    }

    public function getDescription()
    {
        return $this->book->getDescription();
    }

    public function getPrice()
    {
        return $this->book->getPrice();
    }

    public function getFeatures()
    {
        return $this->book->getFeatures();
    }
}

class FeaturedBookDecorator extends BookDecorator
{
    public function getDescription()
    {
        return "⭐ FEATURED: " . $this->book->getDescription();
    }

    public function getPrice()
    {
        return $this->book->getPrice(); // Featured books same price
    }

    public function getFeatures()
    {
        $features = $this->book->getFeatures();
        $features[] = 'Featured on Homepage';
        $features[] = 'Priority in Search Results';
        return $features;
    }
}

class RecommendedBookDecorator extends BookDecorator
{
    public function getDescription()
    {
        return "👍 RECOMMENDED: " . $this->book->getDescription();
    }

    public function getPrice()
    {
        return $this->book->getPrice(); // Recommended books same price
    }

    public function getFeatures()
    {
        $features = $this->book->getFeatures();
        $features[] = 'Staff Recommendation';
        $features[] = 'Editor\'s Choice';
        return $features;
    }
}

class SpecialEditionDecorator extends BookDecorator
{
    private $specialFeatures;

    public function __construct(BookInterface $book, $specialFeatures = [])
    {
        parent::__construct($book);
        $this->specialFeatures = $specialFeatures;
    }

    public function getDescription()
    {
        return "📚 SPECIAL EDITION: " . $this->book->getDescription() . 
               " - Includes special features";
    }

    public function getPrice()
    {
        return $this->book->getPrice() * 1.5; // 50% markup for special edition
    }

    public function getFeatures()
    {
        $features = $this->book->getFeatures();
        $features[] = 'Special Edition';
        $features[] = 'Limited Availability';
        $features[] = 'Collector\'s Item';
        
        if (!empty($this->specialFeatures)) {
            $features = array_merge($features, $this->specialFeatures);
        }
        
        return $features;
    }
}

class BestsellerDecorator extends BookDecorator
{
    public function getDescription()
    {
        return "🏆 BESTSELLER: " . $this->book->getDescription();
    }

    public function getFeatures()
    {
        $features = $this->book->getFeatures();
        $features[] = 'Bestselling Book';
        $features[] = 'High Demand';
        $features[] = 'Multiple Editions Available';
        return $features;
    }
}

class NewArrivalDecorator extends BookDecorator
{
    public function getDescription()
    {
        return "🆕 NEW ARRIVAL: " . $this->book->getDescription();
    }

    public function getFeatures()
    {
        $features = $this->book->getFeatures();
        $features[] = 'Newly Added to Library';
        $features[] = 'First Borrowers Priority';
        return $features;
    }
}

// Factory for creating decorated books
class BookDecoratorFactory
{
    public static function createDecoratedBook($bookData, $decorators = [])
    {
        $book = new BaseBook($bookData);

        foreach ($decorators as $decorator) {
            switch ($decorator) {
                case 'featured':
                    $book = new FeaturedBookDecorator($book);
                    break;
                case 'recommended':
                    $book = new RecommendedBookDecorator($book);
                    break;
                case 'special_edition':
                    $book = new SpecialEditionDecorator($book);
                    break;
                case 'bestseller':
                    $book = new BestsellerDecorator($book);
                    break;
                case 'new_arrival':
                    $book = new NewArrivalDecorator($book);
                    break;
            }
        }

        return $book;
    }
}
