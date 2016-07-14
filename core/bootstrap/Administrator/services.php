<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 HUBzero Foundation, LLC.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @copyright Copyright 2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

return array(
	// Base Services
	'Bootstrap\Administrator\Providers\JoomlaServiceProvider',
	'Bootstrap\Administrator\Providers\EventServiceProvider',
	'Bootstrap\Administrator\Providers\TranslationServiceProvider',
	'Bootstrap\Administrator\Providers\DatabaseServiceProvider',
	'Bootstrap\Administrator\Providers\PluginServiceProvider',
	'Bootstrap\Administrator\Providers\ProfilerServiceProvider',
	'Bootstrap\Administrator\Providers\LogServiceProvider',
	'Bootstrap\Administrator\Providers\RouterServiceProvider',
	'Bootstrap\Administrator\Providers\FilesystemServiceProvider',
	// Admin-specific services
	'Bootstrap\Administrator\Providers\ComponentServiceProvider',
	'Bootstrap\Administrator\Providers\ErrorServiceProvider',
	'Bootstrap\Administrator\Providers\SessionServiceProvider',
	'Bootstrap\Administrator\Providers\AuthServiceProvider',
	'Bootstrap\Administrator\Providers\UserServiceProvider',
	'Bootstrap\Administrator\Providers\DocumentServiceProvider',
	'Bootstrap\Administrator\Providers\ToolbarServiceProvider',
	'Bootstrap\Administrator\Providers\ModuleServiceProvider',
	'Bootstrap\Administrator\Providers\NotificationServiceProvider',
	'Bootstrap\Administrator\Providers\TemplateServiceProvider',
	'Bootstrap\Administrator\Providers\CacheServiceProvider',
	'Bootstrap\Administrator\Providers\EditorServiceProvider',
	'Bootstrap\Administrator\Providers\BuilderServiceProvider',
	'Bootstrap\Administrator\Providers\MailerServiceProvider',
	'Bootstrap\Administrator\Providers\MenuServiceProvider',
	'Bootstrap\Administrator\Providers\FeedServiceProvider',
);