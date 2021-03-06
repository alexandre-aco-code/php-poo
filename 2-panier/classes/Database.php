<?php

	class Database implements IStorage, IConnect
	{
		private $pdo;
		private $productList;

		public function __construct()
		{
			$this->connect();
			$this->productList = new ProductList(new DatabaseProductList());
		}
		
		public function connect() : void
		{
			$this->pdo = new PDO('mysql:host=localhost;dbname=solid', 'root', '');
		}
		


		public function saveCart( array $products ) : void 
		{
			// vider la table
			$this->clearCart();


			$sql = "INSERT INTO cart VALUES(NULL, ?, ?, ?)";
			$query = $this->pdo->prepare($sql);

			foreach( $products as $order )
			{
				$parameters = [ 
					$order->product->getName(), 
					$order->product->getPrice(),
					$order->getQuantity() 
				];
				$query->execute($parameters);
			}
		}

		public function loadCart() : array
		{
			$sql = "SELECT * FROM cart";
			$query = $this->pdo->query($sql);

			$products = $query->fetchAll(PDO::FETCH_ASSOC);

			// var_dump($products); // [ [ 'nom' => ' coca'], [ ....]]
			// [ 'coca' =>  productOrder , 'frites' => productOrder  ]

			$result = [];

			foreach($products as $databaseProduct )
			{
				$product = $this->productList->getProductByName( $databaseProduct['product_name']  );

				$result[ $databaseProduct['product_name'] ] = 
				new ProductOrder( 
					$product , 
					$databaseProduct['quantity'] 
				);
			}

			return $result;
		}

		public function clearCart() : void 
		{
			$this->pdo->query("DELETE FROM cart");
		}


	}
