<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Mdm\MdmProviderRegistry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreImportRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @param MdmProviderRegistry $registry
     * @return array<string, mixed>
     */
    public function rules(MdmProviderRegistry $registry): array
    {
        return [
            'provider' => ['sometimes', 'nullable', 'string', Rule::in($registry->available())],
        ];
    }

    /**
     * @return string|null
     */
    public function providerKey(): ?string
    {
        $value = $this->input('provider');

        return is_string($value) && $value !== '' ? $value : null;
    }
}
