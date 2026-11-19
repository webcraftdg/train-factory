<?php

namespace App\Datapipeline\Provider;

use App\Entity\Activity;
use App\Entity\Block;
use App\Entity\Session;
use App\Entity\Exercise;

class SessionRowProvider
{

    public function __construct(
        private Session $session
    )
    {
    }

    /**
     * get row
     *
     * @return array
     */
    public function getRow() : array
    {
        return $this->getSession($this->session);
    }

    /**
     * get session
     *
     * @param  \App\Entity\Session $session
     *
     * @return array
     */
    protected function getSession(Session $session) :array
    {
        return [
            'id' => $session->getId(),
            'name' => $session->getName(),
            'description' => $session->getDescription(),
            'duration' => $session->getDuration(),
            'target' => $session->getTarget(),
            'blocks' => $this->getBlocks($session)
        ];
    }

    /**
     * get block
     *
     * @param  \App\Entity\Session $session
     *
     * @return array
     */
    protected function getBlocks(Session $session) : array
    {
        $blocks = [];
        $orderedBlocks = $session->getOrderedBlocks();
        /** @var Block $block */
        foreach($orderedBlocks as $block) {
            $blocks[] = [
                'id' => $block->getId(),
                'name' => $block->getName(),
                'duration' => $block->getDuration(),
                'description' => $block->getDescription(),
                'objective' => $block->getObjective(),
                'instruction' => $block->getInstruction(),
                'activities' => $this->getActivities($block)
            ];
        }
        return $blocks;
    }


    /**
     * get activities
     *
     * @param  \App\Entity\Block $block
     *
     * @return array
     */
    protected function getActivities(Block $block) : array
    {
        $activities = [];
        $orderedActivities = $block->getOrderedActivities();
        /** @var Activity $activity */
        foreach($orderedActivities as $activity) {
            $activities[] = [
                'id' => $activity->getId(),
                'name' => $activity->getName(),
                'duration' => $activity->getDuration(),
                'description' => $activity->getDescription(),
                'objective' => $activity->getObjective(),
                'instruction' => $activity->getInstruction(),
                'hydration' => $activity->isHydration(),
                'exercises' => $this->getExercises($activity)
            ];
        }
        return $activities;
    }

    /**
     * get exercises
     *
     * @param  \App\Entity\Activity $activity
     *
     * @return array
     */
    protected function getExercises(Activity $activity) : array
    {
        $exercises = [];
        $orderedExercises = $activity->getOrderedExercises();
        /** @var Exercise $exercise */
        foreach($orderedExercises as $exercise) {
            $exercises[] = [
                'id' => $exercise->getId(),
                'name' => $exercise->getName(),
                'description' => $exercise->getDescription(),
            ];
        }
        return $exercises;
    }
}
