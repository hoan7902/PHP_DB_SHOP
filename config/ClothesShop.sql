CREATE TABLE `Users` (
  `userId` int(11) PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(255),
  `phone` varchar(20) UNIQUE,
  `sex` varchar(10),
  `email` varchar(100) UNIQUE,
  `password` varchar(255),
  `avatar` varchar(500),
  `address` text(500),
  `role` varchar(10) DEFAULT "customer",
  `createdAt` timestamp DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `Orders` (
  `orderId` int(11) PRIMARY KEY AUTO_INCREMENT,
  `status` ENUM('Pending','Accepted','Shipping','Done') DEFAULT 'Pending',
  `phone` varchar(20),
  `cost` float,
  `note` varchar(255),
  `address` text(500),
  `orderTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deliveryTime` datetime
);

CREATE TABLE `Products` (
  `productId` int(11) PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(500),
  `description` text,
  `createdAt` timestamp DEFAULT CURRENT_TIMESTAMP,
  `deleted` boolean DEFAULT 0
);

CREATE TABLE `Collections` (
  `collectionId` int(11) PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(500),
  `description` text
);

CREATE TABLE `Categories` (
  `categoryId` int(11) PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(500) UNIQUE,
  `description` text
);

CREATE TABLE `Carts` (
  `cartId` int(11) PRIMARY KEY AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `productId` int(11) NOT NULL,
  `time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE (`userId`, `productId`)
);

CREATE TABLE `ProductsInOrders` (
  `productId` int(11),
  `orderId` int(11),
  `size` varchar(10),
  `quantity` int(11),
  PRIMARY KEY(`productId`, `orderId`, `size`)
);

CREATE TABLE `UsersHaveOrders` (
  `orderId` int(11) PRIMARY KEY,
  `userId` int(11)
);

CREATE TABLE `ProductsInCollections` (
  `productId` int(11),
  `collectionId` int(11),
  PRIMARY KEY (`productId`, `collectionId`)
);

CREATE TABLE `ProductsInCategories` (
  `productId` int(11),
  `categoryId` int(11),
  PRIMARY KEY (`productId`, `categoryId`)
);

CREATE TABLE `UsersRatingProducts` (
  `userId` int(11),
  `productId` int(11),
  `time` datetime DEFAULT CURRENT_TIMESTAMP,
  `comment` text,
  `star` int,
  PRIMARY KEY (`userId`, `productId`)
);

CREATE TABLE `Details` (
  `sizeName` varchar(10),
  `quantity` int,
  `productId` int(11),
  PRIMARY KEY (`sizeName`, `quantity`, `productId`)
);

CREATE TABLE `Sizes` (
  `sizeName` varchar(10),
  `quantity` int,
  `productId` int(11),
  `price` float,
  PRIMARY KEY (`sizeName`, `quantity`, `productId`, `price`)
);

CREATE TABLE `Images` (
  `productId` int(11),
  `imageLink` varchar(500),
  PRIMARY KEY (`productId`, `imageLink`)
);

ALTER TABLE `Carts` ADD CONSTRAINT `cart_user_fk` FOREIGN KEY (`userId`) REFERENCES `Users` (`userId`) ON DELETE CASCADE;

ALTER TABLE `Carts` ADD CONSTRAINT `cart_product_fk` FOREIGN KEY (`productId`) REFERENCES `Products` (`productId`) ON DELETE CASCADE;

ALTER TABLE `ProductsInOrders` ADD CONSTRAINT `productinorder_product_fk` FOREIGN KEY (`productId`) REFERENCES `Products` (`productId`) ON DELETE CASCADE;

ALTER TABLE `ProductsInOrders` ADD CONSTRAINT `productinorder_order_fk` FOREIGN KEY (`orderId`) REFERENCES `Orders` (`orderId`) ON DELETE CASCADE;

ALTER TABLE `UsersHaveOrders` ADD CONSTRAINT `userhaveorders_order_fk` FOREIGN KEY (`orderId`) REFERENCES `Orders` (`orderId`) ON DELETE CASCADE;

ALTER TABLE `UsersHaveOrders` ADD CONSTRAINT `userhaveorders_user_fk` FOREIGN KEY (`userId`) REFERENCES `Users` (`userId`) ON DELETE CASCADE;

ALTER TABLE `ProductsInCollections` ADD CONSTRAINT `productsincollection_product_fk` FOREIGN KEY (`productId`) REFERENCES `Products` (`productId`) ON DELETE CASCADE;

ALTER TABLE `ProductsInCollections` ADD CONSTRAINT `productsincollection_collection_fk` FOREIGN KEY (`collectionId`) REFERENCES `Collections` (`collectionId`) ON DELETE CASCADE;

ALTER TABLE `ProductsInCategories` ADD CONSTRAINT `productsincategory_product_fk` FOREIGN KEY (`productId`) REFERENCES `Products` (`productId`) ON DELETE CASCADE;

ALTER TABLE `ProductsInCategories` ADD CONSTRAINT `productsincategory_category_fk` FOREIGN KEY (`categoryId`) REFERENCES `Categories` (`categoryId`) ON DELETE CASCADE;

ALTER TABLE `UsersRatingProducts` ADD CONSTRAINT `userratingproducts_user_fk` FOREIGN KEY (`userId`) REFERENCES `Users` (`userId`) ON DELETE CASCADE;

ALTER TABLE `UsersRatingProducts` ADD CONSTRAINT `userratingproducts_product_fk` FOREIGN KEY (`productId`) REFERENCES `Products` (`productId`) ON DELETE CASCADE;

ALTER TABLE `Details` ADD CONSTRAINT `detail_product_fk` FOREIGN KEY (`productId`) REFERENCES `Products` (`productId`) ON DELETE CASCADE;

ALTER TABLE `Sizes` ADD CONSTRAINT `size_product_fk` FOREIGN KEY (`productId`) REFERENCES `Products` (`productId`) ON DELETE CASCADE;

ALTER TABLE `Images` ADD CONSTRAINT `image_product_fk` FOREIGN KEY (`productId`) REFERENCES `Products` (`productId`) ON DELETE CASCADE;
