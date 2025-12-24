<?php

declare(strict_types=1);

namespace Esanj\AppService\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $serviceId = $this->getServiceId();

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('services', 'name')->ignore($serviceId),
            ],
            'client_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('services', 'client_id')->ignore($serviceId),
            ],
            'is_active' => ['boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:service_permissions,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Service name is required.'),
            'name.unique' => __('A service with this name already exists.'),
            'client_id.required' => __('Client ID is required.'),
            'client_id.unique' => __('A service with this client ID already exists.'),
        ];
    }

    private function getServiceId(): ?int
    {
        $service = $this->route('service');

        if ($service === null) {
            return null;
        }

        return is_object($service) ? $service->id : (int) $service;
    }
}