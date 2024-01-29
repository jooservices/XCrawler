<?php

namespace App\Modules\Flickr\God\Providers;

use App\Modules\Core\God\Providers\ProviderInterface;

class Contacts extends AbstractProvider
{
    protected function getList(): void
    {
        for ($index = 1; $index <= 2; $index++) {
            $this->mock->shouldReceive('request')
                ->withArgs(function ($method, $url, $options) use ($index) {
                    return $method === 'POST'
                        && str_contains($url, 'flickr.contacts.getList')
                        && $options['form_params']['per_page'] === 1000
                        && $options['form_params']['page'] === $index
                        && !isset($options['form_params']['exception']);
                })
                ->andReturn(
                    $this->success('flickr_contacts_getList_' . $index . '.json')
                );
        }

        $this->mock->shouldReceive('request')
            ->withArgs(function ($method, $url, $options) {
                return $method === 'POST'
                    && str_contains($url, 'flickr.contacts.getList')
                    && $options['form_params']['exception'] === true;
            })
            ->andThrow($this->exception());
    }

    public function factory(...$args): ProviderInterface
    {
        $this->getList();

        return $this;
    }
}
