<?php

namespace App\Service;

use App\Entity\Block;
use App\Entity\Session;
use App\Repository\BlockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

final class BlockResolver
{
    public function __construct(
        private readonly BlockRepository $blockRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly ActivityResolver $activityResolver,
    ) {
    }

    /**
     * resolve
     *
     * @param  array               $data
     * @param  \App\Entity\Session $entity
     * @param  int                 $entityIndex
     * @param  bool                $clone
     *
     * @return ErrorResolver
     */
    public function resolve(array $data, Session $entity, int $entityIndex = 0, bool $clone = false): ErrorResolver
    {
        $errors = [];
        foreach($data as $index => $item) {
            $id = ($item['id']) ?? null;
            $group = 'block:create';
            if ($id === null || $clone === true) {
                $block = new Block();
                $this->entityManager->persist($block);
            } else {
                $group = 'block:update';
                $block = $this->blockRepository->find($id);
                if ($block === null) {
                    $errors[] = [
                        'path' => 'session['.$entityIndex.'].block['.$index.']',
                        'message' => 'Block not found => '.$id
                    ];
                    continue;
                }
            }
            $block->setName($item['name'] ?? '');
            $block->setDescription($item['description'] ?? null);
            $block->setObjective($item['objective'] ?? null);
            $block->setInstruction($item['instruction'] ?? null);
            $block->setDuration($item['duration'] ?? 0);
            $violations = $this->validator->validate($block, groups:['Default', $group]);
            if (count($violations) > 0) {
                /** @var ConstraintViolationInterface $violation */
                foreach($violations as $violation) {
                    $errors[] = [
                        'path' => sprintf(
                            'sessions[%d].blocks[%d].%s',
                            $entityIndex,
                            $index,
                            $violation->getPropertyPath()
                        ),
                        'message' => $violation->getMessage(),
                    ];
                }
            } else {
                $activities = ($item['activities']) ?? [];
                /** @var ErrorResolver $errorResolver */
                $errorResolver = $this->activityResolver->resolve($activities, $block, $index, $clone);
                if ($errorResolver->isValid() === false) {
                    $errors = array_merge($errors, $errorResolver->errors);
                } else {
                    $entity->addBlock($block);
                }

            }
        }
        return new ErrorResolver($entity, $errors);
    }
}
