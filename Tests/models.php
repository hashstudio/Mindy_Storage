<?php

use Mindy\Orm\Fields\FileField;
use Mindy\Orm\Model;

class StorageModel extends Model
{
    public function getFields()
    {
        return [
            'file' => [
                'class' => FileField::className()
            ]
        ];
    }
}
