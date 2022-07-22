<?php

	namespace umono\multiple\kernel;

	use Pimple\Container;

	class ServiceContainer extends Container
	{
		/**
		 * @var string
		 */
		protected $id;
		/**
		 * @var array
		 */
		protected $providers = [];
		/**
		 * @var array
		 */
		protected $defaultConfig = [];
		/**
		 * @var array
		 */
		protected $userConfig = [];

		/**
		 * Constructor.
		 *
		 * @param array       $config
		 * @param array       $prepends
		 * @param string|null $id
		 */
		public function __construct(array $config = [], array $prepends = [], string $id = null)
		{
			$this->registerProviders($this->getProviders());

			parent::__construct($prepends);

			$this->userConfig = $config;

			$this->id = $id;
		}

		/**
		 * @return string
		 */
		public function getId(): string
		{
			return $this->id ?? $this->id = md5(json_encode($this->userConfig));
		}

		/**
		 * @return array
		 */
		public function getConfig(): array
		{
			return array_replace_recursive([], $this->defaultConfig, $this->userConfig);
		}

		/**
		 * Return all providers.
		 *
		 * @return array
		 */
		public function getProviders(): array
		{
			return $this->providers;
		}

		/**
		 * @param string $id
		 * @param mixed  $value
		 */
		public function rebind(string $id, $value)
		{
			$this->offsetUnset($id);
			$this->offsetSet($id, $value);
		}

		/**
		 * Magic get access.
		 *
		 * @param string $id
		 *
		 * @return mixed
		 */
		public function __get(string $id)
		{
			return $this->offsetGet($id);
		}

		/**
		 * Magic set access.
		 *
		 * @param string $id
		 * @param mixed  $value
		 */
		public function __set(string $id, $value)
		{
			$this->offsetSet($id, $value);
		}

		/**
		 * @param array $providers
		 */
		public function registerProviders(array $providers)
		{
			foreach ($providers as $provider) {
				parent::register(new $provider());
			}
		}
	}