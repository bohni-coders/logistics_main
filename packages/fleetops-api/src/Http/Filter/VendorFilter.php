<?php

namespace Fleetbase\FleetOps\Http\Filter;

use Fleetbase\Http\Filter\Filter;
use Fleetbase\FleetOps\Support\Utils;

class VendorFilter extends Filter
{
    public function queryForInternal()
    {
        $this->builder->where('company_uuid', $this->session->get('company'));
    }
    
    public function query(?string $searchQuery)
    {
        $this->builder->where(function ($query) use ($searchQuery) {
            $query->orWhereHas(
                'user',
                function ($query) use ($searchQuery) {
                    $query->searchWhere(['name', 'email', 'phone', 'address'], $searchQuery);
                }
            );
        });
    }

    public function internalId(?string $internalId)
    {
        $this->builder->searchWhere('internal_id', $internalId);
    }

    public function publicId(?string $publicId)
    {
        $this->builder->searchWhere('public_id', $publicId);
    }

    public function type(?string $type)
    {
        $this->builder->searchWhere('type', $type);
    }

    public function phone(?string $phone)
    {
        $this->builder->searchWhere('phone', $phone);
    }

    public function email(?string $email)
    {
        $this->builder->searchWhere('email', $email);
    }

    public function country(?string $country)
    {
        $this->builder->searchWhere('country', $country);
    }

    public function status(?string $status)
    {
        $this->builder->searchWhere('status', $status);
    }

    public function createdAt($createdAt) 
    {
        $createdAt = Utils::dateRange($createdAt);

        if (is_array($createdAt)) {
            $this->builder->whereBetween('created_at', $createdAt);
        } else {
            $this->builder->whereDate('created_at', $createdAt);
        }
    }

    public function updatedAt($updatedAt) 
    {
        $updatedAt = Utils::dateRange($updatedAt);

        if (is_array($updatedAt)) {
            $this->builder->whereBetween('updated_at', $updatedAt);
        } else {
            $this->builder->whereDate('updated_at', $updatedAt);
        }
    }
}
