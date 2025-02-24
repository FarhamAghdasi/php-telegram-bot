<?php
// core/ShortLinkService.php

class ShortLinkService {
    private $linkFile;
    private $statFile;

    public function __construct($linkFile = __DIR__ . '/../data/links.json', $statFile = __DIR__ . '/../data/stats.json') {
        $this->linkFile = $linkFile;
        $this->statFile = $statFile;

        if (!file_exists($this->linkFile)) {
            file_put_contents($this->linkFile, json_encode([]));
        }
        if (!file_exists($this->statFile)) {
            file_put_contents($this->statFile, json_encode([]));
        }
    }

    public function shortenLink($originalUrl) {
        $links = $this->getLinks();

        if (isset($links[$originalUrl])) {
            return $links[$originalUrl];
        }

        $shortCode = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);
        $links[$originalUrl] = $shortCode;
        $this->saveLinks($links);

        return $shortCode;
    }

    public function getOriginalUrl($shortCode) {
        $links = array_flip($this->getLinks());
        return $links[$shortCode] ?? null;
    }

    public function incrementVisit($shortCode) {
        $stats = $this->getStats();

        if (isset($stats[$shortCode])) {
            $stats[$shortCode]++;
        } else {
            $stats[$shortCode] = 1;
        }

        $this->saveStats($stats);
    }

    public function getVisitCount($shortCode) {
        $stats = $this->getStats();
        return $stats[$shortCode] ?? 0;
    }

    private function getLinks() {
        return json_decode(file_get_contents($this->linkFile), true);
    }

    private function saveLinks($links) {
        file_put_contents($this->linkFile, json_encode($links, JSON_PRETTY_PRINT));
    }

    private function getStats() {
        return json_decode(file_get_contents($this->statFile), true);
    }

    private function saveStats($stats) {
        file_put_contents($this->statFile, json_encode($stats, JSON_PRETTY_PRINT));
    }
}
