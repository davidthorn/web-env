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

namespace Shopware\Components\Password\Encoder;

class Argon2i implements PasswordEncoderInterface
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param array $options
     */
    public function __construct($options = null)
    {
        if ($options !== null) {
            $this->options = $options;
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Argon2i';
    }

    /**
     * @return bool
     */
    public function isCompatible()
    {
        return defined('PASSWORD_ARGON2I');
    }

    /**
     * @param string $password
     * @param string $hash
     *
     * @return bool
     */
    public function isPasswordValid($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * @param string $password
     *
     * @return string
     */
    public function encodePassword($password)
    {
        return password_hash($password, PASSWORD_ARGON2I, $this->options);
    }

    /**
     * @param string $hash
     *
     * @return bool
     */
    public function isReencodeNeeded($hash)
    {
        return password_needs_rehash($hash, PASSWORD_ARGON2I, $this->options);
    }
}
