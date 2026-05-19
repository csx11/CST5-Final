<?php

class Product {
    public int    $id;
    public string $name;
    public string $sku;
    public ?int   $category_id;
    public float  $price;
    public int    $quantity;
    public string $description;
    public ?int   $created_by;
    public string $created_at;
    public string $updated_at;

    
    public string $category_name;

    public function __construct(
        string $name,
        string $sku,
        float  $price,
        int    $quantity,
        string $description   = '',
        ?int   $category_id   = null,
        ?int   $created_by    = null,
        int    $id            = 0,
        string $created_at    = '',
        string $updated_at    = '',
        string $category_name = ''
    ) {
        $this->id            = $id;
        $this->name          = trim($name);
        $this->sku           = strtoupper(trim($sku));
        $this->price         = $price;
        $this->quantity      = $quantity;
        $this->description   = trim($description);
        $this->category_id   = $category_id;
        $this->created_by    = $created_by;
        $this->created_at    = $created_at;
        $this->updated_at    = $updated_at;
        $this->category_name = $category_name;
    }

    
    public function stockStatus(): string {
        if ($this->quantity === 0)  return 'Out of Stock';
        if ($this->quantity <= 10)  return 'Low Stock';
        return 'In Stock';
    }

    
    public function stockClass(): string {
        if ($this->quantity === 0)  return 'badge-danger';
        if ($this->quantity <= 10)  return 'badge-warning';
        return 'badge-success';
    }
}
