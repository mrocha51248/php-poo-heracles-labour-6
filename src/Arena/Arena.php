<?php

namespace App\Arena;

use App\Fighter\Fighter;
use App\Fighter\Hero;
use App\Movable;
use App\Tile\Tile;
use Exception;

abstract class Arena
{
    public const DIRECTIONS = [
        'N' => [0, -1],
        'S' => [0, 1],
        'E' => [1, 0],
        'W' => [-1, 0],
    ];

    private array $monsters;
    private Hero $hero;
    private array $tiles;

    private int $size = 10;

    public function __construct(Hero $hero, array $monsters, array $tiles)
    {
        $this->hero = $hero;
        $this->monsters = $monsters;
        $this->tiles = $tiles;
    }

    public function getTile(int $x, int $y): ?Tile
    {
        foreach ($this->getTiles() as $tile) {
            if ($tile->getX() === $x && $tile->getY() === $y) {
                return $tile;
            }
        }

        return null;
    }

    public function arenaMove(string $direction)
    {
        $this->move($this->getHero(), $direction);

        foreach ($this->getMonsters() as $monster) {
            if ($monster instanceof Movable) {
                $randomDirection = array_rand(self::DIRECTIONS);
                $this->move($monster, $randomDirection);
            }
        }
    }

    public function move(Movable $movable, string $direction)
    {
        $x = $movable->getX();
        $y = $movable->getY();

        $destinationX = $x + self::DIRECTIONS[$direction][0];
        $destinationY = $y + self::DIRECTIONS[$direction][1];

        $destinationTile = $this->getTile($destinationX, $destinationY);
        if ($destinationTile instanceof Tile && !$destinationTile->isCrossable($movable)) {
            throw new Exception('Not crossable tile');
        }

        if ($destinationX < 0 || $destinationX >= $this->getSize() || $destinationY < 0 || $destinationY >= $this->getSize()) {
            throw new Exception('Out of Map');
        }

        foreach ($this->getMonsters() as $monster) {
            if ($monster->getX() == $destinationX && $monster->getY() == $destinationY) {
                throw new Exception('Not free');
            }
        }

        $movable->setX($destinationX);
        $movable->setY($destinationY);
    }

    public function getDistance(Fighter $startFighter, Fighter $endFighter): float
    {
        $Xdistance = $endFighter->getX() - $startFighter->getX();
        $Ydistance = $endFighter->getY() - $startFighter->getY();
        return sqrt($Xdistance ** 2 + $Ydistance ** 2);
    }

    public function battle(int $id): void
    {
        $monster = $this->getMonsters()[$id];
        if ($this->touchable($this->getHero(), $monster)) {
            $this->getHero()->fight($monster);
        } else {
            throw new Exception('Monster out of range');
        }

        if (!$monster->isAlive()) {
            $this->getHero()->setExperience($this->getHero()->getExperience() + $monster->getExperience());
            unset($this->monsters[$id]);
        } else {
            if ($this->touchable($monster, $this->getHero())) {
                $monster->fight($this->getHero());
            } else {
                throw new Exception('Hero out of range');
            }
        }
    }

    public function touchable(Fighter $attacker, Fighter $defenser): bool
    {
        return $this->getDistance($attacker, $defenser) <= $attacker->getRange();
    }

    /**
     * Get the value of monsters
     */
    public function getMonsters(): array
    {
        return $this->monsters;
    }

    /**
     * Get the value of hero
     */
    public function getHero(): Hero
    {
        return $this->hero;
    }


    /**
     * Get the value of size
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Get the value of tiles
     */
    public function getTiles(): array
    {
        return $this->tiles;
    }

    public function addTile(Tile $tile): void
    {
        $this->tiles[] = $tile;
    }

    public function removeTile(Tile $tile): void
    {
        $key = array_search($tile, $this->tiles);
        if ($key !== false) {
            unset($this->tiles[$key]);
        }
    }

    public function replaceTile(Tile $tile): void
    {
        $oldTile = $this->getTile($tile->getX(), $tile->getY());
        $this->removeTile($oldTile);
        $this->addTile($tile);
    }

    public function getAdjacentTiles(Tile $tile): array
    {
        $tiles = [];
        foreach (array_values(self::DIRECTIONS) as $offset) {
            $adjTile = $this->getTile($tile->getX() + $offset[0], $tile->getY() + $offset[1]);
            if ($adjTile instanceof Tile) {
                $tiles[] = $adjTile;
            }
        }
        return $tiles;
    }

    abstract public function isVictory(): bool;
}
