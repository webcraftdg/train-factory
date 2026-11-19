<?php

namespace App\Service\Api;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

final class FormErrorFormatter
{
    public function format(FormInterface $form, bool $deep = true) : array
    {
        $errors = [];
        /** @var FormError $error */
        foreach($form->getErrors($deep) as $index => $error) {
            $errors[] = [
                'path' => $this->getPath($error->getOrigin()),
                'message' => $error->getMessage(),
            ];
        }
        return $errors;
    }


    private function getPath(FormInterface $form): string
    {
        $parts = [];

        while ($form->getParent() !== null) {
            array_unshift($parts, $form->getName());
            $form = $form->getParent();
        }
        $path = '';
        foreach ($parts as $part) {
            if (ctype_digit((string) $part)) {
                $path .= '[' . $part . ']';
            } else {
                $path .= ($path === '' ? '' : '.') . $part;
            }
        }
        return $path;
    }
}
