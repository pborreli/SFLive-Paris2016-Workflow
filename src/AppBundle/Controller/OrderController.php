<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Order;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Workflow\Exception\ExceptionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class OrderController extends Controller
{
    /**
     * @Route("/orders", name="orders")
     */
    public function indexAction()
    {
        $orders = $this->get('doctrine')->getRepository('AppBundle:Order')->findAll();

        return $this->render('order/index.html.twig', [
           'orders' => $orders,
        ]);
    }

    /**
     * @Route("/orders/create", name="order_create")
     */
    public function createAction(Request $request)
    {
        $order = new Order(md5(time()));

        $em = $this->get('doctrine')->getManager();
        $em->persist($order);
        $em->flush();

        return $this->redirect($this->generateUrl('order_show', ['id' => $order->getId()]));
    }

    /**
     * @Route("/orders/{id}", name="order_show")
     */
    public function showAction(Order $order)
    {
        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    /**
     * @Route("/order/apply-transition/{id}", name="order_apply_transition")
     * @Method("POST")
     */
    public function applyTransitionAction(Request $request, Order $order)
    {
        try {
            $this->get('workflow.order')->apply($order, $request->request->get('transition'));

            $this->get('doctrine')->getManager()->flush();
        } catch (ExceptionInterface $e) {
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('order_show', ['id' => $order->getId()]));
    }
}
