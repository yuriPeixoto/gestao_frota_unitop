<?php

namespace App\Traits;

trait SanitizesMonetaryValues
{
    //
    protected function sanitizeMonetaryValues($request, $fields): void
    {
        foreach ($fields as $field) {
            $value = $request->$field;
            $request->merge([
                $field => $value ? str_replace(',', '.', preg_replace('/[^\d,-]/', '', $value)) : null
            ]);
        }
    }
}
