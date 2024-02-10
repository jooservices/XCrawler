<?php

namespace App\Modules\JAV\Services\Movie\Interfaces;

interface MovieEntityInterface
{
    public function getDvdId(): string;

    public function getGenres(): ?array;

    public function getPerformers(): ?array;

    public function getCover(): ?string;

    public function getUrl(): string;

    public function getGallery(): array;
}
