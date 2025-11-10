<?php

namespace App\Traits;

trait ToggleIsActiveOnSoftDelete
{
    public static function bootToggleIsActiveOnSoftDelete()
    {
        static::deleting(function ($model) {
            if (!$model->forceDeleting) {
                $field = $model->getActiveField();
                // Só alterar is_ativo se ainda não foi definido explicitamente para false
                if ($model->$field !== false) {
                    $model->$field = false;
                    $model->saveQuietly();
                }
            }
        });

        static::restoring(function ($model) {
            $field = $model->getActiveField();
            $model->$field = true; // Sempre true ao restaurar
            $model->saveQuietly();
        });
    }

    public function getActiveField()
    {
        return property_exists($this, 'activeField') ? $this->activeField : 'is_ativo';
    }
}
