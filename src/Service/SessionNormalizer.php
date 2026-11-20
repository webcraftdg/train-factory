<?php

namespace App\Service;

use App\Entity\Activity;
use App\Entity\Block;
use App\Entity\Exercise;
use App\Entity\Session;

final class SessionNormalizer
{
    public function normalize(Session $entity) : array
    {
        $data = $entity->toArray();
        $data['blocks'] = $this->normalizeBlocks($entity->getBlocks()->toArray());

        return $data;
    }

    private function normalizeBlocks(array $blocks) : array
    {
        $data = [];
        /** @var Block $block */
        foreach($blocks as $block) {
            $item = $block->toArray();
            $item['activities'] = $this->normalizeActivities($block->getActivities()->toArray());
            $data[] = $item;

        }
        return $data;
    }

    public function normalizeActivities(array $activities) : array
    {
        $data = [];
        /**@var Activity $activity */
        foreach($activities as $activity) {
            $item = $activity->toArray();
            $item['exercises'] = $this->normalizeExercise($activity->getExercises()->toArray());
            $data[] = $item;
        }

        return $data;
    }

    public function normalizeExercise(array $exercises) : array
    {
        $data = [];
        /** @var Exercise $exercise */
        foreach($exercises as $exercise) {
            $data[] = $exercise->toArray();;
        }
        return $data;
    }
}
