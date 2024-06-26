<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Helper
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Helper\Controller;

use Modules\Helper\Models\ReportMapper;
use Modules\Helper\Models\Template;
use Modules\Helper\Models\TemplateMapper;
use Modules\Media\Models\CollectionMapper;
use Modules\Media\Models\Media;
use phpOMS\Contract\RenderableInterface;
use phpOMS\DataStorage\Database\Query\OrderType;
use phpOMS\Localization\ISO639x1Enum;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Utils\StringUtils;
use phpOMS\Views\View;

/**
 * Helper controller class.
 *
 * @package    Modules\Helper
 * @license    OMS License 2.0
 * @link       https://jingga.app
 * @since      1.0.0
 */
final class BackendController extends Controller
{
    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewTemplateList(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        $view->setTemplate('/Modules/Helper/Theme/Backend/helper-list');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1002701001, $request, $response);

        $path      = \str_replace('+', ' ', $request->getDataString('path') ?? '/');
        $templates = TemplateMapper::getAll()
            ->with('createdBy')
            ->with('tags')
            ->with('tags/title')
            ->where('virtualPath', $path)
            ->where('tags/title/language', $response->header->l11n->language)
            ->executeGetArray();

        list($collection, $parent) = CollectionMapper::getCollectionsByPath($path);

        $view->data['parent']      = $parent;
        $view->data['collections'] = $collection;
        $view->data['path']        = $path;
        $view->data['reports']     = $templates;
        $view->data['account']     = $this->app->accountManager->get($request->header->account);

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewTemplateCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        $view->setTemplate('/Modules/Helper/Theme/Backend/helper-template-create');
        $view->data['nav']          = $this->app->moduleManager->get('Navigation')->createNavigationMid(1002701001, $request, $response);

        $editor               = new \Modules\Editor\Theme\Backend\Components\Editor\BaseView($this->app->l11nManager, $request, $response);
        $view->data['editor'] = $editor;

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewReportCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        $view->setTemplate('/Modules/Helper/Theme/Backend/helper-create');
        $view->data['nav']          = $this->app->moduleManager->get('Navigation')->createNavigationMid(1002701001, $request, $response);

        $editor               = new \Modules\Editor\Theme\Backend\Components\Editor\BaseView($this->app->l11nManager, $request, $response);
        $view->data['editor'] = $editor;

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @throws \Exception
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewHelperReport(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        //$file = preg_replace('([^\w\s\d\-_~,;:\.\[\]\(\).])', '', $template->getName());

        /** @var Template $template */
        $template = TemplateMapper::get()
            ->with('createdBy')
            ->with('tags')
            ->with('tags/title')
            ->with('source')
            ->with('source/sources')
            ->where('id', (int) $request->getData('id'))
            ->where('tags/title/language', $response->header->l11n->language)
            ->execute();

        $view->setTemplate('/Modules/Helper/Theme/Backend/helper-view');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1002701001, $request, $response);

        $view->data['unit'] = $this->app->unitId;

        /** @var array<string, \Modules\Media\Models\Media> $tcoll */
        $tcoll = [];

        /** @var \Modules\Media\Models\Media[] $files */
        $files = $template->source->getSources();

        /** @var \Modules\Media\Models\Media $tMedia */
        foreach ($files as $tMedia) {
            $lowerPath = \strtolower($tMedia->getPath());

            if (StringUtils::endsWith($lowerPath, '.lang.php')) {
                $tcoll['lang'] = $tMedia;
            } elseif (StringUtils::endsWith($lowerPath, '.cfg.json')) {
                $tcoll['cfg'] = $tMedia;
            } elseif (StringUtils::endsWith($lowerPath, 'worker.php')) {
                $tcoll['worker'] = $tMedia;
            } elseif (StringUtils::endsWith($lowerPath, '.xlsx.php') || StringUtils::endsWith($lowerPath, '.xls.php')) {
                $tcoll['excel'] = $tMedia;
            } elseif (StringUtils::endsWith($lowerPath, '.docx.php') || StringUtils::endsWith($lowerPath, '.doc.php')) {
                $tcoll['word'] = $tMedia;
            } elseif (StringUtils::endsWith($lowerPath, '.pptx.php') || StringUtils::endsWith($lowerPath, '.ppt.php')) {
                $tcoll['powerpoint'] = $tMedia;
            } elseif (StringUtils::endsWith($lowerPath, '.pdf.php')) {
                $tcoll['pdf'] = $tMedia;
            } elseif (StringUtils::endsWith($lowerPath, '.csv.php')) {
                $tcoll['csv'] = $tMedia;
            } elseif (StringUtils::endsWith($lowerPath, '.json.php')) {
                $tcoll['json'] = $tMedia;
            } elseif (StringUtils::endsWith($lowerPath, '.tpl.php')) {
                $tcoll['template'] = $tMedia;
            } elseif (StringUtils::endsWith($lowerPath, '.css')) {
                $tcoll['css'] = $tMedia;
            } elseif (StringUtils::endsWith($lowerPath, '.js')) {
                $tcoll['js'] = $tMedia;
            } elseif (StringUtils::endsWith($lowerPath, '.sqlite') || StringUtils::endsWith($lowerPath, '.db')) {
                $tcoll['db'] = $tMedia;
            }
        }

        $rcoll  = [];
        $report = null;
        if (!$template->isStandalone) {
            if (!isset($tcoll['template'])) {
                return $view;
            }

            /** @var \Modules\Helper\Models\Report $report */
            $report = ReportMapper::get()
                ->where('template', $template->id)
                ->sort('id', OrderType::DESC)
                ->limit(1)
                ->execute();

            if ($report->id > 0) {
                /** @var Media[] $files */
                $files = $report->source->getSources();

                foreach ($files as $media) {
                    $rcoll[$media->name . '.' . $media->extension] = $media;
                }
            }
        }

        $view->data['report']   = $report;
        $view->data['tcoll']    = $tcoll;
        $view->data['rcoll']    = $rcoll;
        $view->data['lang']     = ISO639x1Enum::tryFromValue($request->getDataString('lang')) ?? $request->header->l11n->language;
        $view->data['template'] = $template;
        $view->data['nav']      = $this->app->moduleManager->get('Navigation')->createNavigationMid(1002701001, $request, $response);

        return $view;
    }
}
