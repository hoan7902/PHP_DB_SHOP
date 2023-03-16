CREATE TABLE `User` (
  `userId` int(11) PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(255),
  `phone` varchar(20),
  `sex` varchar(10),
  `email` varchar(100) UNIQUE,
  `password` varchar(255),
  `avatar` varchar(500),
  `address` text(500),
  `role` varchar(10) DEFAULT "customer",
  `createdAt` timestamp DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `Order` (
  `orderId` int(11) PRIMARY KEY AUTO_INCREMENT,
  `status` varchar(32),
  `phone` varchar(20),
  `cost` float,
  `note` varchar(255),
  `address` text(500),
  `orderTime` datetime,
  `deliveryTime` datetime
);

CREATE TABLE `Product` (
  `productId` int(11) PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(500),
  `description` text,
  `createdAt` timestamp DEFAULT CURRENT_TIMESTAMP,
  `deleted` boolean DEFAULT 0
);

CREATE TABLE `Collection` (
  `collectionId` int(11) PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(500),
  `description` text
);

CREATE TABLE `Category` (
  `categoryId` int(11) PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(500) UNIQUE,
  `description` text
);

CREATE TABLE `Cart` (
  `cartId` int(11) PRIMARY KEY AUTO_INCREMENT,
  `userId` int(11),
  `productId` int(11),
  `time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE `ProductInOrder` (
  `productId` int(11) PRIMARY KEY AUTO_INCREMENT,
  `orderId` int(11)
);

CREATE TABLE `UserHaveOrders` (
  `orderId` int(11) PRIMARY KEY AUTO_INCREMENT,
  `userId` int(11)
);

CREATE TABLE `ProductsInCollection` (
  `productId` int(11),
  `collectionId` int(11),
  PRIMARY KEY (`productId`, `collectionId`)
);

CREATE TABLE `ProductsInCategory` (
  `productId` int(11),
  `categoryId` int(11),
  PRIMARY KEY (`productId`, `categoryId`)
);

CREATE TABLE `UserRatingProducts` (
  `userId` int(11),
  `productId` int(11),
  `time` datetime,
  `comment` text,
  `star` int,
  PRIMARY KEY (`userId`, `productId`)
);

CREATE TABLE `Detail` (
  `sizeName` varchar(10),
  `quantity` int,
  `productId` int(11),
  PRIMARY KEY (`sizeName`, `quantity`, `productId`)
);

CREATE TABLE `Size` (
  `sizeName` varchar(10),
  `quantity` int,
  `productId` int(11),
  `price` float,
  PRIMARY KEY (`sizeName`, `quantity`, `productId`, `price`)
);

CREATE TABLE `Image` (
  `productId` int(11),
  `imageLink` varchar(500),
  PRIMARY KEY (`productId`, `imageLink`)
);

ALTER TABLE `Cart` ADD CONSTRAINT `cart_user_fk` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`) ON DELETE CASCADE;

ALTER TABLE `Cart` ADD CONSTRAINT `cart_product_fk` FOREIGN KEY (`productId`) REFERENCES `Product` (`productId`) ON DELETE CASCADE;

ALTER TABLE `ProductInOrder` ADD CONSTRAINT `productinorder_product_fk` FOREIGN KEY (`productId`) REFERENCES `Product` (`productId`) ON DELETE CASCADE;

ALTER TABLE `ProductInOrder` ADD CONSTRAINT `productinorder_order_fk` FOREIGN KEY (`orderId`) REFERENCES `Order` (`orderId`) ON DELETE CASCADE;

ALTER TABLE `UserHaveOrders` ADD CONSTRAINT `userhaveorders_order_fk` FOREIGN KEY (`orderId`) REFERENCES `Order` (`orderId`) ON DELETE CASCADE;

ALTER TABLE `UserHaveOrders` ADD CONSTRAINT `userhaveorders_user_fk` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`) ON DELETE CASCADE;

ALTER TABLE `ProductsInCollection` ADD CONSTRAINT `productsincollection_product_fk` FOREIGN KEY (`productId`) REFERENCES `Product` (`productId`) ON DELETE CASCADE;

ALTER TABLE `ProductsInCollection` ADD CONSTRAINT `productsincollection_collection_fk` FOREIGN KEY (`collectionId`) REFERENCES `Collection` (`collectionId`) ON DELETE CASCADE;

ALTER TABLE `ProductsInCategory` ADD CONSTRAINT `productsincategory_product_fk` FOREIGN KEY (`productId`) REFERENCES `Product` (`productId`) ON DELETE CASCADE;

ALTER TABLE `ProductsInCategory` ADD CONSTRAINT `productsincategory_category_fk` FOREIGN KEY (`categoryId`) REFERENCES `Category` (`categoryId`) ON DELETE CASCADE;

ALTER TABLE `UserRatingProducts` ADD CONSTRAINT `userratingproducts_user_fk` FOREIGN KEY (`userId`) REFERENCES `User` (`userId`) ON DELETE CASCADE;

ALTER TABLE `UserRatingProducts` ADD CONSTRAINT `userratingproducts_product_fk` FOREIGN KEY (`productId`) REFERENCES `Product` (`productId`) ON DELETE CASCADE;

ALTER TABLE `Detail` ADD CONSTRAINT `detail_product_fk` FOREIGN KEY (`productId`) REFERENCES `Product` (`productId`) ON DELETE CASCADE;

ALTER TABLE `Size` ADD CONSTRAINT `size_product_fk` FOREIGN KEY (`productId`) REFERENCES `Product` (`productId`) ON DELETE CASCADE;

ALTER TABLE `Image` ADD CONSTRAINT `image_product_fk` FOREIGN KEY (`productId`) REFERENCES `Product` (`productId`) ON DELETE CASCADE;
