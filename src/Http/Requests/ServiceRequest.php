<?php

namespace Esanj\AppService\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->method() == 'POST') {
            return [
                'name' => ['required', 'string', 'max:255', 'unique:services,name'],
                'client_id' => ['required', 'string', 'max:255', 'unique:services,client_id'],
                'is_active' => ['boolean'],
                'permissions' => ['nullable', 'array'],
                'permissions.*' => ['exists:service_permissions,id'],
            ];
        }

        if ($this->method() == 'PUT') {
            $serviceId = $this->route('service')?->id ?? $this->route('service');

            return [
                'name' => ['required', 'string', 'max:255', 'unique:services,name,' . $serviceId],
                'client_id' => ['required', 'string', 'max:255', 'unique:services,client_id,' . $serviceId],
                'is_active' => ['boolean'],
                'permissions' => ['nullable', 'array'],
                'permissions.*' => ['exists:service_permissions,id'],
            ];
        }
    }
}
