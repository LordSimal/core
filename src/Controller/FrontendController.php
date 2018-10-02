<?php

declare(strict_types=1);

namespace Bolt\Controller;

use Bolt\Configuration\Config;
use Bolt\Entity\Content;
use Bolt\Repository\ContentRepository;
use Bolt\Repository\FieldRepository;
use Bolt\TemplateChooser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontendController extends AbstractController
{
    /** @var Config */
    private $config;

    /** @var TemplateChooser */
    private $templateChooser;

    public function __construct(Config $config, TemplateChooser $templateChooser)
    {
        $this->config = $config;
        $this->templateChooser = $templateChooser;
    }

    /**
     * @Route("/", methods={"GET"}, name="homepage")
     */
    public function homepage()
    {
        $homepage = $this->getOption('theme/homepage') ?: $this->getOption('general/homepage');

        $template = $this->templateChooser->homepage('');

        return $this->render('@theme/index.twig', []);
    }

    /**
     * @Route("/content", methods={"GET"}, name="listing")
     */
    public function contentListing(ContentRepository $content, Request $request): Response
    {
        $page = (int) $request->query->get('page', 1);

        /** @var Content $records */
        $records = $content->findLatest($page);

        return $this->render('@theme/listing.twig', ['records' => $records ]);
    }

    /**
     * @Route("/record/{id<[1-9]\d*>}", methods={"GET"}, name="record_by_id")
     * @Route("/record/{slug<[a-z0-9_-]+>}", methods={"GET"}, name="record")
     *
     * @param ContentRepository $contentRepository
     * @param FieldRepository   $fieldRepository
     * @param null              $id
     * @param null              $slug
     *
     * @return Response
     */
    public function record(ContentRepository $contentRepository, FieldRepository $fieldRepository, $id = null, $slug = null): Response
    {
        if ($id) {
            $record = $contentRepository->findOneBy(['id' => $id]);
        } elseif ($slug) {
            $field = $fieldRepository->findOneBySlug($slug);
            $record = $field->getContent();
//            $record = $contentRepository->findOneBySlug($slug);
        }

        $recordSlug = $record->getDefinition()['singular_slug'];

        $context = [
            'record' => $record,
            $recordSlug => $record,
        ];

        return $this->render('@theme/record.twig', $context);
    }

    /**
     * Shortcut for {@see \Bolt\Config::get}.
     *
     * @param string $path
     * @param mixed  $default
     *
     * @return string|int|array|null
     */
    protected function getOption($path, $default = null)
    {
        dump($this->config->get($path, $default));

        return $this->config->get($path, $default);
    }

    /**
     * Renders a view.
     *
     * @final
     *
     * @param string|array  $view
     * @param array         $parameters
     * @param Response|null $response
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @return Response
     */
    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        $themepath = sprintf(
            '%s/%s',
            $this->config->getPath('themes'),
            $this->config->get('general/theme')
        );

        /** @var \Twig_Environment $twig */
        $twig = $this->contain
