<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Tests\Functional\Components\Theme;

class InstallerTest extends Base
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testSynchronizeThemeDirectory()
    {
        $repository = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $repository->expects(static::any())
            ->method('findOneBy')
            ->willReturn(null);

        $entityManager = $this->getEntityManager();
        $entityManager->expects(static::exactly(2))
            ->method('persist');

        $entityManager->expects(static::exactly(2))
            ->method('flush');

        $entityManager->expects(static::once())
            ->method('getRepository')
            ->willReturn($repository);

        $configurator = $this->getConfigurator();

        $installer = new \Shopware\Components\Theme\Installer(
            $entityManager,
            $configurator,
            Shopware()->Container()->get(\Shopware\Components\Theme\PathResolver::class),
            Shopware()->Container()->get(\Shopware\Components\Theme\Util::class),
            $this->getSnippetHandler(),
            Shopware()->Container()->get(\Shopware\Components\Theme\Service::class),
            Shopware()->Container()->getParameter('shopware.snippet')
        );
        //creates a directory iterator for the default theme directory (engine/Shopware/Themes)
        $directories = new \DirectoryIterator(
            __DIR__ . '/Themes/'
        );

        $themes = $this->invokeMethod(
            $installer,
            'synchronizeThemeDirectories',
            [$directories]
        );

        static::assertCount(2, $themes);
    }
}
