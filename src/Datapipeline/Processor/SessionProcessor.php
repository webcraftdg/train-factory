<?php

namespace App\Datapipeline\Processor;

use App\Datapipeline\Provider\SessionRowProvider;
use App\Repository\SessionRepository;
use Exception;
use webcraftdg\dataPipeline\exceptions\ProcessorException;
use webcraftdg\dataPipeline\exceptions\ProcessorResult;
use webcraftdg\dataPipeline\interfaces\ProcessorInterface;

class SessionProcessor implements ProcessorInterface
{
    public function getName(): string
    {
        return 'session-load';
    }

    public function process(array $row, array $options = []): ProcessorResult
    {
        if (isset($row['id']) === false ) {
            throw new ProcessorException('Session processor, session id not found in $rows');
        }
        try {
            /** @var SessionRepository $repository */
            $repository = ($options['repository']) ?? null;
            if ($repository !== null) {
                $session = $repository->find($row['id']);
                $provider = new SessionRowProvider($session);
                $rowBuilded = $provider->getRow();
                return new ProcessorResult($rowBuilded);
            } else {
                throw new ProcessorException('Session processor, repository not found in $options');
            }

        } catch(Exception $e) {
            throw new ProcessorException($e->getMessage(), $e->getCode());
        }
    }
}
