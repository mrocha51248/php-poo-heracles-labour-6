<?php

namespace App\Tile;

class Grass extends Tile
{
    protected string $image = 'grass.png';  
    protected bool $digged = false;

    public function dig(): void
    {
        $this->setDigged(true);
        $this->setImage('hole.png');
    }

    /**
     * Get the value of digged
     *
     * @return bool
     */
    public function isDigged(): bool
    {
        return $this->digged;
    }

    /**
     * Set the value of digged
     *
     * @param bool $digged
     *
     * @return self
     */
    public function setDigged(bool $digged): self
    {
        $this->digged = $digged;

        return $this;
    }
}
