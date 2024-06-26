<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Helper\tests\Controller;

use Model\CoreSettings;
use Modules\Admin\Models\AccountPermission;
use phpOMS\Account\Account;
use phpOMS\Account\AccountManager;
use phpOMS\Account\PermissionType;
use phpOMS\Application\ApplicationAbstract;
use phpOMS\DataStorage\Session\HttpSession;
use phpOMS\Dispatcher\Dispatcher;
use phpOMS\Event\EventManager;
use phpOMS\Localization\L11nManager;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Module\ModuleAbstract;
use phpOMS\Module\ModuleManager;
use phpOMS\Router\WebRouter;
use phpOMS\System\File\Local\Directory;
use phpOMS\Utils\TestUtils;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Modules\Helper\Controller\ApiController::class)]
#[\PHPUnit\Framework\Attributes\TestDox('Modules\Helper\tests\Controller\ApiControllerTest: Helper api controller')]
final class ApiControllerTest extends \PHPUnit\Framework\TestCase
{
    protected ApplicationAbstract $app;

    /**
     * @var \Modules\Helper\Controller\ApiController
     */
    protected ModuleAbstract $module;

    protected static ?int $depreciationHelper = 0;

    protected static ?int $depreciationHelper2 = 0;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        $this->app = new class() extends ApplicationAbstract
        {
            protected string $appName = 'Api';

            protected int $appId = 1;
        };

        $this->app->dbPool         = $GLOBALS['dbpool'];
        $this->app->unitId         = 1;
        $this->app->accountManager = new AccountManager($GLOBALS['session']);
        $this->app->appSettings    = new CoreSettings();
        $this->app->moduleManager  = new ModuleManager($this->app, __DIR__ . '/../../../../Modules/');
        $this->app->dispatcher     = new Dispatcher($this->app);
        $this->app->eventManager   = new EventManager($this->app->dispatcher);
        $this->app->eventManager->importFromFile(__DIR__ . '/../../../../Web/Api/Hooks.php');
        $this->app->sessionManager = new HttpSession(36000);
        $this->app->l11nManager    = new L11nManager();

        $account = new Account();
        TestUtils::setMember($account, 'id', 1);

        $permission       = new AccountPermission();
        $permission->unit = 1;
        $permission->app  = 1;
        $permission->setPermission(
            PermissionType::READ
            | PermissionType::CREATE
            | PermissionType::MODIFY
            | PermissionType::DELETE
            | PermissionType::PERMISSION
        );

        $account->addPermission($permission);

        $this->app->accountManager->add($account);
        $this->app->router = new WebRouter();

        $this->module = $this->app->moduleManager->get('Helper');

        TestUtils::setMember($this->module, 'app', $this->app);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testStandaloneTemplateCreate() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('name', \ucfirst('depreciation'));
        $request->setData('standalone', true);
        $request->setData('tags', '[{"title": "TestTitle", "color": "#f0f", "language": "en"}, {"id": 1}]');

        $files = [];

        if (!\is_dir(__DIR__ . '/temp')) {
            \mkdir(__DIR__ . '/temp');
        }

        $helperFiles = \scandir(__DIR__ . '/../depreciation');
        foreach ($helperFiles as $filePath) {
            if (!\is_file(__DIR__ . '/../depreciation/' . $filePath)
                || $filePath === '..' || $filePath === '.'
            ) {
                continue;
            }

            \copy(__DIR__ . '/../depreciation/' . $filePath, __DIR__ . '/temp/' . $filePath);

            $files[] = [
                'error'    => \UPLOAD_ERR_OK,
                'type'     => \substr($filePath, \strrpos($filePath, '.') + 1),
                'name'     => $filePath,
                'tmp_name' => __DIR__ . '/temp/' . $filePath,
                'size'     => \filesize(__DIR__ . '/temp/' . $filePath),
            ];
        }

        TestUtils::setMember($request, 'files', $files);

        $this->module->apiTemplateCreate($request, $response);
        self::assertGreaterThan(0, self::$depreciationHelper = $response->getDataArray('')['response']?->id);

        \rmdir(__DIR__ . '/temp');
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testTemplateCreate() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('name', \ucfirst('depreciation'));
        $request->setData('standalone', false);
        $request->setData('tags', '[{"title": "TestTitle", "color": "#f0f", "language": "en"}, {"id": 1}]');

        $files = [];

        if (!\is_dir(__DIR__ . '/temp')) {
            \mkdir(__DIR__ . '/temp');
        }

        $helperFiles = \scandir(__DIR__ . '/../depreciation');
        foreach ($helperFiles as $filePath) {
            if (!\is_file(__DIR__ . '/../depreciation/' . $filePath)
                || $filePath === '..' || $filePath === '.'
            ) {
                continue;
            }

            \copy(__DIR__ . '/../depreciation/' . $filePath, __DIR__ . '/temp/' . $filePath);

            $files[] = [
                'error'    => \UPLOAD_ERR_OK,
                'type'     => \substr($filePath, \strrpos($filePath, '.') + 1),
                'name'     => $filePath,
                'tmp_name' => __DIR__ . '/temp/' . $filePath,
                'size'     => \filesize(__DIR__ . '/temp/' . $filePath),
            ];
        }

        TestUtils::setMember($request, 'files', $files);

        $this->module->apiTemplateCreate($request, $response);
        self::assertGreaterThan(0, self::$depreciationHelper2 = $response->getDataArray('')['response']?->id);

        \rmdir(__DIR__ . '/temp');
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testTemplateCreateInvalidPermission() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 9999;
        $request->setData('name', \ucfirst('depreciation'));
        $request->setData('standalone', false);
        $request->setData('tags', '[{"title": "TestTitle", "color": "#f0f", "language": "en"}, {"id": 1}]');

        $files = [];

        if (!\is_dir(__DIR__ . '/temp')) {
            \mkdir(__DIR__ . '/temp');
        }

        $helperFiles = \scandir(__DIR__ . '/../depreciation');
        foreach ($helperFiles as $filePath) {
            if (!\is_file(__DIR__ . '/../depreciation/' . $filePath)
                || $filePath === '..' || $filePath === '.'
            ) {
                continue;
            }

            \copy(__DIR__ . '/../depreciation/' . $filePath, __DIR__ . '/temp/' . $filePath);

            $files[] = [
                'error'    => \UPLOAD_ERR_OK,
                'type'     => \substr($filePath, \strrpos($filePath, '.') + 1),
                'name'     => $filePath,
                'tmp_name' => __DIR__ . '/temp/' . $filePath,
                'size'     => \filesize(__DIR__ . '/temp/' . $filePath),
            ];
        }

        TestUtils::setMember($request, 'files', $files);

        $this->module->apiTemplateCreate($request, $response);
        self::assertEquals(RequestStatusCode::R_403, $response->header->status);

        Directory::delete(__DIR__ . '/temp');
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testExportPdf() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('id', self::$depreciationHelper);
        $request->setData('type', 'pdf');

        $this->module->apiHelperExport($request, $response);
        self::assertTrue(\stripos($response->header->get('Content-disposition')[0] ?? '', 'pdf') !== false);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testExportXlsx() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('id', self::$depreciationHelper);
        $request->setData('type', 'xlsx');

        $this->module->apiHelperExport($request, $response);
        self::assertTrue(\stripos($response->header->get('Content-disposition')[0] ?? '', 'xlsx') !== false);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testExportDocx() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('id', self::$depreciationHelper);
        $request->setData('type', 'docx');

        $this->module->apiHelperExport($request, $response);
        self::assertTrue(\stripos($response->header->get('Content-disposition')[0] ?? '', 'docx') !== false);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testExportPptx() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('id', self::$depreciationHelper);
        $request->setData('type', 'pptx');

        $this->module->apiHelperExport($request, $response);
        self::assertTrue(\stripos($response->header->get('Content-disposition')[0] ?? '', 'pptx') !== false);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testExportCsv() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('id', self::$depreciationHelper);
        $request->setData('type', 'csv');

        $this->module->apiHelperExport($request, $response);
        self::assertTrue(\stripos($response->header->get('Content-disposition')[0] ?? '', 'csv') !== false);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testExportJson() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('id', self::$depreciationHelper);
        $request->setData('type', 'json');

        $this->module->apiHelperExport($request, $response);
        self::assertTrue(\stripos($response->header->get('Content-disposition')[0] ?? '', 'json') !== false);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testExportInvalidPermission() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 99999;
        $request->setData('id', self::$depreciationHelper);
        $request->setData('type', 'csv');

        $this->module->apiHelperExport($request, $response);
        self::assertEquals(RequestStatusCode::R_403, $response->header->status);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testExportOtherType() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('id', self::$depreciationHelper);
        $request->setData('type', 'invalid');

        $this->module->apiHelperExport($request, $response);
        self::assertEquals(RequestStatusCode::R_200, $response->header->status); // is html "export"/render
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testExportInvalidData() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('invalid', '1');

        $this->module->apiHelperExport($request, $response);
        self::assertEquals(RequestStatusCode::R_400, $response->header->status);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testApiTemplateCreateInvalidData() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('invalid', '1');

        $this->module->apiTemplateCreate($request, $response);
        self::assertEquals(RequestStatusCode::R_400, $response->header->status);
    }

    #[\PHPUnit\Framework\Attributes\Depends('testTemplateCreate')]
    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testReportCreate() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('name', \ucfirst('depreciation-report'));
        $request->setData('template', self::$depreciationHelper2);

        if (!\is_file(__DIR__ . '/reportData_tmp.csv')) {
            \copy(__DIR__ . '/../depreciation/reportData.csv', __DIR__ . '/reportData_tmp.csv');
        }

        TestUtils::setMember($request, 'files', [
            [
                'name'     => 'reportData.csv',
                'type'     => 'csv',
                'error'    => \UPLOAD_ERR_OK,
                'tmp_name' => __DIR__ . '/reportData_tmp.csv',
                'size'     => \filesize(__DIR__ . '/reportData_tmp.csv'),
            ],
        ]);

        $this->module->apiReportCreate($request, $response);
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testExportOtherTypeNotStandalone() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('id', self::$depreciationHelper2);
        $request->setData('type', 'invalid');

        $this->module->apiHelperExport($request, $response);
        self::assertEquals(RequestStatusCode::R_200, $response->header->status); // is html "export"/render
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testReportCreateInvalidPermission() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 9999;
        $request->setData('name', \ucfirst('depreciation-report'));
        $request->setData('template', self::$depreciationHelper2);

        if (!\is_file(__DIR__ . '/reportData_tmp.csv')) {
            \copy(__DIR__ . '/../depreciation/reportData.csv', __DIR__ . '/reportData_tmp.csv');
        }

        TestUtils::setMember($request, 'files', [
            [
                'name'     => 'reportData.csv',
                'type'     => 'csv',
                'error'    => \UPLOAD_ERR_OK,
                'tmp_name' => __DIR__ . '/reportData_tmp.csv',
                'size'     => \filesize(__DIR__ . '/reportData_tmp.csv'),
            ],
        ]);

        $this->module->apiReportCreate($request, $response);
        self::assertEquals(RequestStatusCode::R_403, $response->header->status);

        if (\is_file(__DIR__ . '/reportData_tmp.csv')) {
            \unlink(__DIR__ . '/reportData_tmp.csv');
        }
    }

    #[\PHPUnit\Framework\Attributes\Group('module')]
    public function testApiReportCreateInvalidData() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest();

        $request->header->account = 1;
        $request->setData('invalid', '1');

        $this->module->apiReportCreate($request, $response);
        self::assertEquals(RequestStatusCode::R_400, $response->header->status);
    }
}
