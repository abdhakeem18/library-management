<?php

// Builder Pattern for Complex Book Objects

class BookBuilder
{
    private $book;

    public function __construct()
    {
        $this->book = new stdClass();
        $this->book->metadata = [];
        $this->book->features = [];
    }

    public function setBasicInfo($title, $author, $isbn, $category)
    {
        $this->book->title = $title;
        $this->book->author = $author;
        $this->book->isbn = $isbn;
        $this->book->category = $category;
        return $this;
    }

    public function setDescription($description)
    {
        $this->book->description = $description;
        return $this;
    }

    public function setPublicationInfo($datePublished, $publisher = null, $edition = null)
    {
        $this->book->date_published = $datePublished;
        $this->book->metadata['publisher'] = $publisher;
        $this->book->metadata['edition'] = $edition;
        return $this;
    }

    public function setQuantity($qty, $availableQty = null)
    {
        $this->book->qty = $qty;
        $this->book->available_qty = $availableQty ?? $qty;
        return $this;
    }

    public function addReview($rating, $reviewText, $reviewer)
    {
        if (!isset($this->book->metadata['reviews'])) {
            $this->book->metadata['reviews'] = [];
        }
        $this->book->metadata['reviews'][] = [
            'rating' => $rating,
            'review' => $reviewText,
            'reviewer' => $reviewer,
            'date' => date('Y-m-d')
        ];
        return $this;
    }

    public function addTags(array $tags)
    {
        $this->book->metadata['tags'] = $tags;
        return $this;
    }

    public function markAsFeatured()
    {
        $this->book->features[] = 'Featured';
        return $this;
    }

    public function markAsRecommended()
    {
        $this->book->features[] = 'Recommended';
        return $this;
    }

    public function markAsSpecialEdition()
    {
        $this->book->features[] = 'Special Edition';
        return $this;
    }

    public function setLanguage($language)
    {
        $this->book->metadata['language'] = $language;
        return $this;
    }

    public function setPages($pages)
    {
        $this->book->metadata['pages'] = $pages;
        return $this;
    }

    public function build()
    {
        // Validate required fields
        if (empty($this->book->title) || empty($this->book->author)) {
            throw new Exception("Title and Author are required");
        }

        $this->book->metadata = json_encode($this->book->metadata);
        $this->book->features = json_encode($this->book->features);
        
        return $this->book;
    }

    public function reset()
    {
        $this->book = new stdClass();
        $this->book->metadata = [];
        $this->book->features = [];
        return $this;
    }
}

// Director class for common book configurations
class BookDirector
{
    private $builder;

    public function __construct(BookBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function buildSimpleBook($title, $author, $isbn, $category, $qty)
    {
        return $this->builder
            ->setBasicInfo($title, $author, $isbn, $category)
            ->setQuantity($qty)
            ->build();
    }

    public function buildFeaturedBook($title, $author, $isbn, $category, $description, $qty)
    {
        return $this->builder
            ->setBasicInfo($title, $author, $isbn, $category)
            ->setDescription($description)
            ->setQuantity($qty)
            ->markAsFeatured()
            ->markAsRecommended()
            ->build();
    }

    public function buildDetailedBook($title, $author, $isbn, $category, $description, 
                                     $datePublished, $publisher, $edition, $qty, $tags = [])
    {
        return $this->builder
            ->setBasicInfo($title, $author, $isbn, $category)
            ->setDescription($description)
            ->setPublicationInfo($datePublished, $publisher, $edition)
            ->setQuantity($qty)
            ->addTags($tags)
            ->build();
    }
}
