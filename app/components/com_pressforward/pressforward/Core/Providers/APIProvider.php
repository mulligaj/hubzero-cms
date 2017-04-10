<?php
namespace PressForward\Core\Providers;

use Intraxia\Jaxion\Contract\Core\Container as Container;
use Intraxia\Jaxion\Assets\Register as Assets;
//use Intraxia\Jaxion\Assets\ServiceProvider as ServiceProvider;
use Intraxia\Jaxion\Assets\ServiceProvider as ServiceProvider;

use PressForward\Core\API\PostExtension;
use PressForward\Core\API\FeedEndpoint;
use PressForward\Core\API\ItemEndpoint;
use PressForward\Core\API\PFEndpoint;
use PressForward\Core\API\FolderExtension;

class APIProvider extends ServiceProvider {

	public function register( Container $container ){
		$container->share(
			'api.pf_endpoint',
			function( $container ){
				return new PFEndpoint( $container->fetch('controller.metas') );
			}
		);

		$container->share(
			'api.post_extension',
			function( $container ){
				return new PostExtension( $container->fetch('controller.metas') );
			}
		);
		$container->share(
			'api.feed_endpoint',
			function( $container ){
				return new FeedEndpoint( $container->fetch('controller.metas') );
			}
		);
		$container->share(
			'api.item_endpoint',
			function( $container ){
				return new ItemEndpoint( $container->fetch('controller.metas') );
			}
		);
		$container->share(
			'api.folder_extension',
			function( $container ){
				return new FolderExtension( $container->fetch('controller.metas') );
			}
		);
	}

}
