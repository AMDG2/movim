<?php

use Respect\Validation\Validator;

use Movim\Widget\Base;
use Movim\Session;

class Share extends Base
{
    public function load()
    {
        $this->addjs('share.js');
    }

    public function ajaxGet($link)
    {
        $validate_url = Validator::url();

        if ($validate_url->validate($link)
        && substr($link, 0, 4) == 'http') {
            $session = Session::start();
            $session->set('share_url', $link);
            $this->rpc('Share.redirect', $this->route('news'));
        } else {
            $uri = parse_url($link);

            if ($uri && $uri['scheme'] == 'xmpp') {
                if (isset($uri['query'])) {
                    if ($uri['query'] == 'join') {
                        $this->rpc(
                            'MovimUtils.redirect',
                            $this->route(
                                'chat',
                                [$uri['path'], 'room']
                            )
                        );

                        return;
                    }

                    $params = explodeQueryParams($uri['query']);

                    if (isset($params['node']) && isset($params['item'])) {
                        $this->rpc(
                            'MovimUtils.redirect',
                            $this->route(
                                'post',
                                [$uri['path'], $params['node'], $params['item']]
                            )
                        );
                    }

                    if (isset($params['node'])) {
                        $this->rpc(
                            'MovimUtils.redirect',
                            $this->route(
                                'community',
                                [$uri['path'], $params['node']]
                            )
                        );
                    }
                } else {
                    $this->rpc(
                        'MovimUtils.redirect',
                        $this->route(
                            'contact',
                            $uri['path']
                        )
                    );
                }
            }
        }
    }
}
