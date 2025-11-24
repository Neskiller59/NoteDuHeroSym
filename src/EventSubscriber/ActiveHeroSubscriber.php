<?php

namespace App\EventSubscriber;

use App\Repository\HeroRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class ActiveHeroSubscriber implements EventSubscriberInterface
{
    private RequestStack $requestStack;
    private HeroRepository $heroRepository;
    private Environment $twig;

    public function __construct(RequestStack $requestStack, HeroRepository $heroRepository, Environment $twig)
    {
        $this->requestStack = $requestStack;
        $this->heroRepository = $heroRepository;
        $this->twig = $twig;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $session = $this->requestStack->getSession();
        $heroId = $session->get('active_hero_id');

        $activeHero = $heroId ? $this->heroRepository->find($heroId) : null;
        $this->twig->addGlobal('activeHero', $activeHero);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
