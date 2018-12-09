<?php
/**
 * Keep Basket
 * Copyright (c) shopbase
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ShopbaseKeepBasket;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;

class ShopbaseKeepBasket extends Plugin
{
    /**
     * Executed on activate plugin
     *
     * @param ActivateContext $context
     */
    public function activate(ActivateContext $context)
    {
        $this->container->get('dbal_connection')->prepare('DELETE FROM `s_order_basket`')->execute();
    }

    /**
     * Executed on deactivate plugin
     *
     * @param DeactivateContext $context
     */
    public function deactivate(DeactivateContext $context)
    {
        $this->container->get('dbal_connection')->prepare('DELETE FROM `s_order_basket` WHERE sessionID = :keepSession')->execute(['keepSession' => 'keep_basket']);
    }

    /**
     * Get the subscribed events
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_Frontend_Account_Logout' => 'onSubscribeLogout',
            'Enlight_Controller_Action_PostDispatch_Frontend' => 'onSubscribeLogin',
        ];
    }

    /**
     * Extend the login process
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function onSubscribeLogin(\Enlight_Event_EventArgs $args)
    {
        $sessionID = Shopware()->Session()->get('sessionId');
        $userId = Shopware()->Session()->sUserId;

        if($userId !== NULL) {
            $doctrine = $args->getSubject()->get('dbal_connection');

            $sql = 'SELECT * FROM s_order_basket WHERE userID = :userID AND sessionID != :sessionID';
            $statement = $doctrine->prepare($sql);
            $statement->execute([
                'userID' => $userId,
                'sessionID' => $sessionID
            ]);

            $result = $statement->fetchAll();

            foreach($result as $key => $item) {
                $sql = 'UPDATE s_order_basket SET sessionID = :sessionID WHERE id = :id';
                $statement = $doctrine->prepare($sql);
                $statement->execute([
                    'sessionID' => $sessionID,
                    'id' => $item['id'],
                ]);
            }

            if(count($result) > 0) {
                $args->getSubject()->redirect('/checkout/cart');
                return;
            }
        }
    }

    /**
     * Extend the logout process
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function onSubscribeLogout(\Enlight_Event_EventArgs $args)
    {
        $sessionID = Shopware()->Session()->get('sessionId');
        $doctrine = $args->getSubject()->get('dbal_connection');
        $sql = 'UPDATE s_order_basket SET sessionID = :keepSessionID WHERE sessionID = :sessionID';
        $statement = $doctrine->prepare($sql);
        $statement->execute([
            'keepSessionID' => 'keep_basket',
            'sessionID' => $sessionID,
        ]);
    }
}
