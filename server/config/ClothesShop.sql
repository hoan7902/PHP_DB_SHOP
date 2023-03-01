CREATE TABLE `User` (
  `userId` int(11) PRIMARY KEY,
  `name` varchar(255),
  `phone` varchar(20),
  `sex` varchar(10),
  `email` varchar(100) UNIQUE,
  `password` varchar(255),
  `avatar` varchar(500),
  `address` text(500),
  `role` varchar(10) DEFAULT "customer"
);

CREATE TABLE `Order` (
  `orderId` int(11) PRIMARY KEY,
  `status` varchar(32),
  `phone` varchar(20),
  `cost` float,
  `note` varchar(255),
  `address` text(500),
  `orderTime` datetime,
  `deliveryTime` datetime
);

CREATE TABLE `Product` (
  `productId` int(11) PRIMARY KEY,
  `name` varchar(500),
  `price` float,
  `description` text
);

CREATE TABLE `Collection` (
  `collectionId` int(11) PRIMARY KEY,
  `name` varchar(500),
  `description` text
);

CREATE TABLE `Category` (
  `categoryId` int(11) PRIMARY KEY,
  `name` varchar(500),
  `description` text
);

CREATE TABLE `ProductInOrder` (
  `productId` int(11) PRIMARY KEY,
  `orderId` int(11)
);

CREATE TABLE `userHaveOrders` (
  `orderId` int(11) PRIMARY KEY,
  `userId` int(11)
);

CREATE TABLE `productsInCollection` (
  `productId` int(11),
  `collectionId` int(11),
  PRIMARY KEY (`productId`, `collectionId`)
);

CREATE TABLE `productsInCategory` (
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
  PRIMARY KEY (`sizeName`, `quantity`, `productId`)
);

CREATE TABLE `Image` (
  `productId` int(11),
  `imageLink` varchar(500),
  PRIMARY KEY (`productId`, `imageLink`)
);

ALTER TABLE `ProductInOrder` ADD FOREIGN KEY (`productId`) REFERENCES `Product` (`productId`);

ALTER TABLE `ProductInOrder` ADD FOREIGN KEY (`orderId`) REFERENCES `Order` (`orderId`);

ALTER TABLE `userHaveOrders` ADD FOREIGN KEY (`orderId`) REFERENCES `Order` (`orderId`);

ALTER TABLE `userHaveOrders` ADD FOREIGN KEY (`userId`) REFERENCES `User` (`userId`);

ALTER TABLE `Product` ADD FOREIGN KEY (`productId`) REFERENCES `productsInCollection` (`productId`);

ALTER TABLE `productsInCollection` ADD FOREIGN KEY (`collectionId`) REFERENCES `Collection` (`collectionId`);

ALTER TABLE `Product` ADD FOREIGN KEY (`productId`) REFERENCES `productsInCategory` (`productId`);

ALTER TABLE `Category` ADD FOREIGN KEY (`categoryId`) REFERENCES `productsInCategory` (`categoryId`);

ALTER TABLE `User` ADD FOREIGN KEY (`userId`) REFERENCES `UserRatingProducts` (`userId`);

ALTER TABLE `Product` ADD FOREIGN KEY (`productId`) REFERENCES `UserRatingProducts` (`productId`);

ALTER TABLE `Product` ADD FOREIGN KEY (`productId`) REFERENCES `Detail` (`productId`);

ALTER TABLE `Product` ADD FOREIGN KEY (`productId`) REFERENCES `Size` (`productId`);

ALTER TABLE `Product` ADD FOREIGN KEY (`productId`) REFERENCES `Image` (`productId`);
