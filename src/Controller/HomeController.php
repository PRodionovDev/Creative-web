<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Movie;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Interfaces\RouteCollectorInterface;
use Twig\Environment;
use Carbon\Carbon;

class HomeController
{
    public function __construct(
        private RouteCollectorInterface $routeCollector,
        private Environment $twig,
        private EntityManagerInterface $em
    ) {}

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $reflection = new \ReflectionClass($this);
            $data = $this->twig->render('home/index.html.twig', [
                'trailers' => $this->fetchData(),
                'date' => Carbon::now()->isoFormat('DD.MM.YYYY kk:mm:ss'),
                'controller' => $reflection->getShortName(),
                'method' => $reflection->getMethod(__FUNCTION__)->name
            ]);
        } catch (\Exception $e) {
            throw new HttpBadRequestException($request, $e->getMessage(), $e);
        }

        $response->getBody()->write($data);

        return $response;
    }

    public function trailer(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $this->twig->render('home/trailer.html.twig', [
                'trailer' => $this->getMovie((int) $request->getAttribute('id'))
            ]);
        } catch (\Exception $e) {
            throw new HttpBadRequestException($request, $e->getMessage(), $e);
        }

        $response->getBody()->write($data);

        return $response;
    }

    protected function fetchData(): Collection
    {
        $data = $this->em->getRepository(Movie::class)
            ->findBy([], orderBy: ['pubDate' => 'DESC'], limit: 10);

        return new ArrayCollection($data);
    }

    protected function getMovie(int $id): Movie
    {
        $trailer = $this->em->getRepository(Movie::class)
            ->findOneBy(['id' => $id]);

        return $trailer;
    }
}
