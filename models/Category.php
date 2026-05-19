<?php

class Category {
    public int    $id;
    public string $name;
    public string $description;
    public string $created_at;

    public function __construct(
        string $name,
        string $description = '',
        int    $id          = 0,
        string $created_at  = ''
    ) {
        $this->id          = $id;
        $this->name        = trim($name);
        $this->description = trim($description);
        $this->created_at  = $created_at;
    }
}
