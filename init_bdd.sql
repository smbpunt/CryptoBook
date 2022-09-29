INSERT INTO public.user (id, email, roles, password, last_connected_at) VALUES
(1, 'nicolau.bastien@hotmail.fr', '["ROLE_USER","ROLE_ADMIN"]', '$2y$13$Wr7mrxja0Mg6lcSREmH8iOIBdDFSQQ3W/jYLrj7cmlguQWAEgaJsa', '2022-08-30 09:11:04'),
(2, 'tirael13300@gmail.com', '["ROLE_USER","ROLE_ADMIN"]', '$2y$13$o.trOT5VosLBh/rwTDjLKOmclteE1TalM89yfFftIwu9oK2CHH.R.', NULL),
(3, 'ruben.amar95@gmail.com', '[]', '$2y$13$vV0ix8toJ7sb3eY3qlxa0uNwZuh1sODz.K4C8n871ZlVgh8/XmP0W', NULL),
(4, 'romain.bnrt@gmail.com', '["ROLE_USER","ROLE_ADMIN"]', '$2y$13$h/osAL.GbscpWfmrwxL9WehmNZN/Em/fa6RBcLgR5Xk5CQn/J33kK', NULL),
(5, 'goldengrams09@gmail.com', '["ROLE_USER","ROLE_ADMIN"]', '$2y$13$IOg35YeHdC1lb59jyZS.0eFLfA1txPkNlh0jF3wEBu3Z8zKhGyHI2', NULL),
(6, 'marie.dichiara@live.fr', '[]', '$2y$13$rsX/5siwVK6JpYyqjylUZ.GTZBanmgaMhlv4crWCarvxnmofmlL3O', NULL),
(7, 'icotirael@gmail.com', '[]', '$2y$13$qu.gD2O2akYOaI7g0CVybeW9N8/wUtKcy/uochw1mO.OKjCf8nn2.', NULL),
(8, 'mad.natali@hotmail.fr', '[]', '$2y$13$rK.yQqUIYxxNr/9AVYL1buY9ad2MOLmBKDTuEf5h11YxtTZXnxYzy', NULL),
(9, 'kelthia7@gmail.com', '[]', '$2y$13$bFKrG7SANV8O4ClhcLmWhOlwBuVe7FkKrAT0nd/i2TbaFs90cyv0G', NULL),
(10, 'paulalessio@protonmail.fr', '[]', '$2y$13$0iDO3k5SDmlSG.qiqImvDOa/c0duzziNhRC0U0KWYmL33avOflxzq', NULL),
(11, 'paulalessio@protonmail.com', '[]', '$2y$13$dL.hBeXBnkii779Qh6y28.AWU/S/VyDq4xF8s7IR5ELIM0CQVUS/S', NULL),
(12, 'roro13140@hotmail.fr', '[]', '$2y$13$VPVc8ByjII8NLjl3zFYvn.3Olpl7SikkawY7WdkGIsuh6zX3X6wra', NULL),
(13, 'rollandalexandre.crypto@gmail.com', '[]', '$2y$13$SpPA3Wb7qUfEiZmasyGTfeTf2jOZMlauyI8WjD/PFF/c5tdKcAzsS', NULL),
(14, 'thomasgrans@hotmail.fr', '[]', '$2y$13$sAi5QunTFU.AfiVZuc3dDuQL1IiLjuPh4OkghYAwjKV.MfUtjrFMe', NULL),
(15, 'romeo.dasneves@gmail.com', '[]', '$2y$13$e6ZISlNUCHPTJaNIBruO1.uLGmx3pwWXmbCtyGY30qd2FHeNEcSCK', NULL),
(16, 'grim.anais@gmail.com', '[]', '$2y$13$HpNwroBL5ImD9NyTH6ooEul8pUEMV/z/s/JVkmCkSbcKXFSplxV9e', NULL),
(17, 'corbani.romain@gmail.com', '[]', '$2y$13$u2vy9FNwozkwah9gO.oH4OxlU2wjTaC9oeJr0o8TDlppFgPwBJpv2', NULL),
(18, 'smbsmbsmb10@protonmail.com', '["ROLE_USER"]', '$2y$13$QMguk4Vd.ixDBpCyMPyvJOZFHZ879W5ArEiTlboIQVAggBor7H5Qy', NULL),
(20, 'tristanav.crypto@gmail.com', '["ROLE_USER"]', '$2y$13$KdlND1FxSctX4/WK7VfB0.ulg91Dsng4oXET0gZBjpH6nnI03L.re', NULL),
(21, 'Zolojinn@digitaltechnologi.space', '["ROLE_USER"]', '$2y$13$PogQR/BBIoHzWB0x91MGXu0rCdCtDamZOZA72JhOOHl1pkiuSMKfi', NULL),
(22, 'diacheadpe1987@mail.ru', '["ROLE_USER"]', '$2y$13$BdmX3qa67cFnIvdMR3gROeanhKSrFzJ4XX7vvlYC9YkcupfCE8g0S', NULL);


INSERT INTO public.cryptocurrency (id, libelle_coingecko, libelle, price_usd, mcap_usd, url_img_thumb, url_img_small, url_img_large, symbol, is_stable, color) VALUES
(1, 'bitcoin', 'Bitcoin', 20052, 1252735170305, 'https://assets.coingecko.com/coins/images/1/thumb/bitcoin.png?1547033579', 'https://assets.coingecko.com/coins/images/1/small/bitcoin.png?1547033579', 'https://assets.coingecko.com/coins/images/1/large/bitcoin.png?1547033579', 'btc', false, '#f2b50d'),
(2, 'ethereum', 'Ethereum', 1492.46, 486072073050, 'https://assets.coingecko.com/coins/images/279/thumb/ethereum.png?1595348880', 'https://assets.coingecko.com/coins/images/279/small/ethereum.png?1595348880', 'https://assets.coingecko.com/coins/images/279/large/ethereum.png?1595348880', 'eth', false, '#3915bc'),
(3, 'tether', 'Tether', 1.001, 70048796323, 'https://assets.coingecko.com/coins/images/325/thumb/Tether-logo.png?1598003707', 'https://assets.coingecko.com/coins/images/325/small/Tether-logo.png?1598003707', 'https://assets.coingecko.com/coins/images/325/large/Tether-logo.png?1598003707', 'usdt', true, '#03ac84'),
(4, 'solana', 'Solana', 31.49, 52325598274, 'https://assets.coingecko.com/coins/images/4128/thumb/coinmarketcap-solana-200.png?1616489452', 'https://assets.coingecko.com/coins/images/4128/small/coinmarketcap-solana-200.png?1616489452', 'https://assets.coingecko.com/coins/images/4128/large/coinmarketcap-solana-200.png?1616489452', 'sol', false, '#ab00c2'),
(5, 'polkadot', 'Polkadot', 7.07, 46691291670, 'https://assets.coingecko.com/coins/images/12171/thumb/aJGBjJFU_400x400.jpg?1597804776', 'https://assets.coingecko.com/coins/images/12171/small/aJGBjJFU_400x400.jpg?1597804776', 'https://assets.coingecko.com/coins/images/12171/large/aJGBjJFU_400x400.jpg?1597804776', 'dot', false, '#ffffff'),
(6, 'litecoin', 'Litecoin', 55.75, 14248488456, 'https://assets.coingecko.com/coins/images/2/thumb/litecoin.png?1547033580', 'https://assets.coingecko.com/coins/images/2/small/litecoin.png?1547033580', 'https://assets.coingecko.com/coins/images/2/large/litecoin.png?1547033580', 'ltc', false, NULL),
(8, 'the-graph', 'The Graph', 0.103464, 4799268852, 'https://assets.coingecko.com/coins/images/13397/thumb/Graph_Token.png?1608145566', 'https://assets.coingecko.com/coins/images/13397/small/Graph_Token.png?1608145566', 'https://assets.coingecko.com/coins/images/13397/large/Graph_Token.png?1608145566', 'grt', false, NULL),
(9, 'binancecoin', 'Binance Coin', 279.48, 76535741674, 'https://assets.coingecko.com/coins/images/825/thumb/binance-coin-logo.png?1547034615', 'https://assets.coingecko.com/coins/images/825/small/binance-coin-logo.png?1547034615', 'https://assets.coingecko.com/coins/images/825/large/binance-coin-logo.png?1547034615', 'bnb', false, '#ffe224'),
(10, 'flow', 'Flow', 1.79, 3713955908, 'https://assets.coingecko.com/coins/images/13446/thumb/5f6294c0c7a8cda55cb1c936_Flow_Wordmark.png?1631696776', 'https://assets.coingecko.com/coins/images/13446/small/5f6294c0c7a8cda55cb1c936_Flow_Wordmark.png?1631696776', 'https://assets.coingecko.com/coins/images/13446/large/5f6294c0c7a8cda55cb1c936_Flow_Wordmark.png?1631696776', 'flow', false, NULL),
(11, 'crypto-com-chain', 'Crypto.com Coin', 0.121128, 4825372387, 'https://assets.coingecko.com/coins/images/7310/thumb/cypto.png?1547043960', 'https://assets.coingecko.com/coins/images/7310/small/cypto.png?1547043960', 'https://assets.coingecko.com/coins/images/7310/large/cypto.png?1547043960', 'cro', false, NULL),
(12, 'chiliz', 'Chiliz', 0.209363, 1656179879, 'https://assets.coingecko.com/coins/images/8834/thumb/Chiliz.png?1561970540', 'https://assets.coingecko.com/coins/images/8834/small/Chiliz.png?1561970540', 'https://assets.coingecko.com/coins/images/8834/large/Chiliz.png?1561970540', 'chz', false, NULL),
(13, 'vechain', 'VeChain', 0.02445824, 8537368746, 'https://assets.coingecko.com/coins/images/1167/thumb/VeChain-Logo-768x725.png?1547035194', 'https://assets.coingecko.com/coins/images/1167/small/VeChain-Logo-768x725.png?1547035194', 'https://assets.coingecko.com/coins/images/1167/large/VeChain-Logo-768x725.png?1547035194', 'vet', false, NULL),
(14, 'matic-network', 'Polygon', 0.796502, 12240581734, 'https://assets.coingecko.com/coins/images/4713/thumb/matic-token-icon.png?1624446912', 'https://assets.coingecko.com/coins/images/4713/small/matic-token-icon.png?1624446912', 'https://assets.coingecko.com/coins/images/4713/large/matic-token-icon.png?1624446912', 'matic', false, '#74009e'),
(15, 'elrond-erd-2', 'Elrond', 51.99, 5031555566, 'https://assets.coingecko.com/coins/images/12335/thumb/elrond3_360.png?1626341589', 'https://assets.coingecko.com/coins/images/12335/small/elrond3_360.png?1626341589', 'https://assets.coingecko.com/coins/images/12335/large/elrond3_360.png?1626341589', 'egld', false, NULL),
(18, 'chainlink', 'Chainlink', 6.53, 13647369311, 'https://assets.coingecko.com/coins/images/877/thumb/chainlink-new-logo.png?1547034700', 'https://assets.coingecko.com/coins/images/877/small/chainlink-new-logo.png?1547034700', 'https://assets.coingecko.com/coins/images/877/large/chainlink-new-logo.png?1547034700', 'link', false, '#0008ff'),
(19, 'curve-dao-token', 'Curve DAO Token', 1.037, 1797794559, 'https://assets.coingecko.com/coins/images/12124/thumb/Curve.png?1597369484', 'https://assets.coingecko.com/coins/images/12124/small/Curve.png?1597369484', 'https://assets.coingecko.com/coins/images/12124/large/Curve.png?1597369484', 'crv', false, '#ff0000'),
(20, 'aave', 'Aave', 83.14, 4479095450, 'https://assets.coingecko.com/coins/images/12645/thumb/AAVE.png?1601374110', 'https://assets.coingecko.com/coins/images/12645/small/AAVE.png?1601374110', 'https://assets.coingecko.com/coins/images/12645/large/AAVE.png?1601374110', 'aave', false, '#ff42ef'),
(21, 'uniswap', 'Uniswap', 6.11, 12899475661, 'https://assets.coingecko.com/coins/images/12504/thumb/uniswap-uni.png?1600306604', 'https://assets.coingecko.com/coins/images/12504/small/uniswap-uni.png?1600306604', 'https://assets.coingecko.com/coins/images/12504/large/uniswap-uni.png?1600306604', 'uni', false, NULL),
(22, 'avalanche-2', 'Avalanche', 18.5, 13811676658, 'https://assets.coingecko.com/coins/images/12559/thumb/coin-round-red.png?1604021818', 'https://assets.coingecko.com/coins/images/12559/small/coin-round-red.png?1604021818', 'https://assets.coingecko.com/coins/images/12559/large/coin-round-red.png?1604021818', 'AVAX', false, '#cc0000'),
(23, 'celsius-degree-token', 'Celsius Network', 1.31, 1988396516, 'https://assets.coingecko.com/coins/images/3263/thumb/CEL_logo.png?1609598753', 'https://assets.coingecko.com/coins/images/3263/small/CEL_logo.png?1609598753', 'https://assets.coingecko.com/coins/images/3263/large/CEL_logo.png?1609598753', 'cel', false, NULL),
(24, 'terra-luna', 'Terra', 0.00013461, 16397050007, 'https://assets.coingecko.com/coins/images/8284/thumb/luna1557227471663.png?1567147072', 'https://assets.coingecko.com/coins/images/8284/small/luna1557227471663.png?1567147072', 'https://assets.coingecko.com/coins/images/8284/large/luna1557227471663.png?1567147072', 'luna', false, '#fff266'),
(25, 'cosmos', 'Cosmos', 10.83, 10793027865, 'https://assets.coingecko.com/coins/images/1481/thumb/cosmos_hub.png?1555657960', 'https://assets.coingecko.com/coins/images/1481/small/cosmos_hub.png?1555657960', 'https://assets.coingecko.com/coins/images/1481/large/cosmos_hub.png?1555657960', 'atom', false, '#404040'),
(26, 'celo', 'Celo', 0.812677, 2092775172, 'https://assets.coingecko.com/coins/images/11090/thumb/icon-celo-CELO-color-500.png?1592293590', 'https://assets.coingecko.com/coins/images/11090/small/icon-celo-CELO-color-500.png?1592293590', 'https://assets.coingecko.com/coins/images/11090/large/icon-celo-CELO-color-500.png?1592293590', 'celo', false, NULL),
(27, 'kusama', 'Kusama', 47.32, 3303710433, 'https://assets.coingecko.com/coins/images/9568/thumb/m4zRhP5e_400x400.jpg?1576190080', 'https://assets.coingecko.com/coins/images/9568/small/m4zRhP5e_400x400.jpg?1576190080', 'https://assets.coingecko.com/coins/images/9568/large/m4zRhP5e_400x400.jpg?1576190080', 'ksm', false, '#000000'),
(29, 'coin-capsule', 'Ternoa', 0.01215618, 26829178, 'https://assets.coingecko.com/coins/images/15921/thumb/e55393fa-7b4d-40f5-9f36-9a8a6bdcb570.png?1622430581', 'https://assets.coingecko.com/coins/images/15921/small/e55393fa-7b4d-40f5-9f36-9a8a6bdcb570.png?1622430581', 'https://assets.coingecko.com/coins/images/15921/large/e55393fa-7b4d-40f5-9f36-9a8a6bdcb570.png?1622430581', 'caps', false, NULL),
(30, 'ultra', 'Ultra', 0.379932, 162091370, 'https://assets.coingecko.com/coins/images/4480/thumb/Ultra.png?1563356418', 'https://assets.coingecko.com/coins/images/4480/small/Ultra.png?1563356418', 'https://assets.coingecko.com/coins/images/4480/large/Ultra.png?1563356418', 'uos', false, '#7a52d1'),
(31, 'adamant', 'Adamant', 0.241755, 0, 'https://assets.coingecko.com/coins/images/15225/thumb/adamant.png?1620136256', 'https://assets.coingecko.com/coins/images/15225/small/adamant.png?1620136256', 'https://assets.coingecko.com/coins/images/15225/large/adamant.png?1620136256', 'addy', false, NULL),
(32, 'rarible', 'Rarible', 2.55, 95478617, 'https://assets.coingecko.com/coins/images/11845/thumb/Rari.png?1594946953', 'https://assets.coingecko.com/coins/images/11845/small/Rari.png?1594946953', 'https://assets.coingecko.com/coins/images/11845/large/Rari.png?1594946953', 'rari', false, NULL),
(33, 'beefy-finance', 'Beefy.Finance', 389.7, 101533474, 'https://assets.coingecko.com/coins/images/12704/thumb/token.png?1601876182', 'https://assets.coingecko.com/coins/images/12704/small/token.png?1601876182', 'https://assets.coingecko.com/coins/images/12704/large/token.png?1601876182', 'bifi', false, NULL),
(34, 'pax-gold', 'PAX Gold', 1729.59, 322007660, 'https://assets.coingecko.com/coins/images/9519/thumb/paxg.PNG?1568542565', 'https://assets.coingecko.com/coins/images/9519/small/paxg.PNG?1568542565', 'https://assets.coingecko.com/coins/images/9519/large/paxg.PNG?1568542565', 'paxg', true, '#d1af25'),
(35, 'apwine', 'APWine', 0.159545, 19863750, 'https://assets.coingecko.com/coins/images/15597/thumb/ApWine.png?1621340387', 'https://assets.coingecko.com/coins/images/15597/small/ApWine.png?1621340387', 'https://assets.coingecko.com/coins/images/15597/large/ApWine.png?1621340387', 'apw', false, NULL),
(36, 'aavegotchi', 'Aavegotchi', 1.27, 138968700, 'https://assets.coingecko.com/coins/images/12467/thumb/ghst_200.png?1600750321', 'https://assets.coingecko.com/coins/images/12467/small/ghst_200.png?1600750321', 'https://assets.coingecko.com/coins/images/12467/large/ghst_200.png?1600750321', 'ghst', false, NULL),
(37, 'star-atlas-dao', 'Star Atlas DAO', 0.383019, 139879803, 'https://assets.coingecko.com/coins/images/17789/thumb/POLIS.jpg?1629256006', 'https://assets.coingecko.com/coins/images/17789/small/POLIS.jpg?1629256006', 'https://assets.coingecko.com/coins/images/17789/large/POLIS.jpg?1629256006', 'polis', false, NULL),
(38, 'solrise-finance', 'Solrise Finance', 0.02291975, 24825302, 'https://assets.coingecko.com/coins/images/15762/thumb/9989.png?1621825696', 'https://assets.coingecko.com/coins/images/15762/small/9989.png?1621825696', 'https://assets.coingecko.com/coins/images/15762/large/9989.png?1621825696', 'slrs', false, NULL),
(39, 'aleph', 'Aleph.im', 0.262216, 51496655, 'https://assets.coingecko.com/coins/images/11676/thumb/Monochram-aleph.png?1608483725', 'https://assets.coingecko.com/coins/images/11676/small/Monochram-aleph.png?1608483725', 'https://assets.coingecko.com/coins/images/11676/large/Monochram-aleph.png?1608483725', 'aleph', false, NULL),
(41, 'aurory', 'Aurory', 1.87, 129787653, 'https://assets.coingecko.com/coins/images/19324/thumb/logo.png?1635076945', 'https://assets.coingecko.com/coins/images/19324/small/logo.png?1635076945', 'https://assets.coingecko.com/coins/images/19324/large/logo.png?1635076945', 'aury', false, '#ff00d0'),
(43, 'usd-coin', 'USD Coin', 1.001, 33103655284, 'https://assets.coingecko.com/coins/images/6319/thumb/USD_Coin_icon.png?1547042389', 'https://assets.coingecko.com/coins/images/6319/small/USD_Coin_icon.png?1547042389', 'https://assets.coingecko.com/coins/images/6319/large/USD_Coin_icon.png?1547042389', 'usdc', true, '#2775ca'),
(44, 'fantom', 'Fantom', 0.275189, 6699310171, 'https://assets.coingecko.com/coins/images/4001/thumb/Fantom.png?1558015016', 'https://assets.coingecko.com/coins/images/4001/small/Fantom.png?1558015016', 'https://assets.coingecko.com/coins/images/4001/large/Fantom.png?1558015016', 'ftm', false, NULL),
(45, 'dai', 'Dai', 1.001, 7790393198, 'https://assets.coingecko.com/coins/images/9956/thumb/dai-multi-collateral-mcd.png?1574218774', 'https://assets.coingecko.com/coins/images/9956/small/dai-multi-collateral-mcd.png?1574218774', 'https://assets.coingecko.com/coins/images/9956/large/dai-multi-collateral-mcd.png?1574218774', 'dai', true, '#f4b731'),
(46, 'xdai', 'xDAI', 0.989525, 0, 'https://assets.coingecko.com/coins/images/11062/thumb/xdai.png?1614727492', 'https://assets.coingecko.com/coins/images/11062/small/xdai.png?1614727492', 'https://assets.coingecko.com/coins/images/11062/large/xdai.png?1614727492', 'xdai', true, '#e4cb4e'),
(47, 'terrausd', 'TerraUSD', 0.02460712, 2800318959, 'https://assets.coingecko.com/coins/images/12681/thumb/UST.png?1601612407', 'https://assets.coingecko.com/coins/images/12681/small/UST.png?1601612407', 'https://assets.coingecko.com/coins/images/12681/large/UST.png?1601612407', 'ust', true, '#5493f7'),
(48, 'jarvis-synthetic-euro', 'Jarvis Synthetic Euro', 0.947049, 0, 'https://assets.coingecko.com/coins/images/15725/thumb/jEUR.png?1634046044', 'https://assets.coingecko.com/coins/images/15725/small/jEUR.png?1634046044', 'https://assets.coingecko.com/coins/images/15725/large/jEUR.png?1634046044', 'jeur', true, '#00149d'),
(49, 'celo-dollar', 'Celo Dollar', 0.992524, 117315980, 'https://assets.coingecko.com/coins/images/13161/thumb/icon-celo-dollar-color-1000-circle-cropped.png?1605771134', 'https://assets.coingecko.com/coins/images/13161/small/icon-celo-dollar-color-1000-circle-cropped.png?1605771134', 'https://assets.coingecko.com/coins/images/13161/large/icon-celo-dollar-color-1000-circle-cropped.png?1605771134', 'cusd', true, '#45cd85'),
(50, 'moonriver', 'Moonriver', 12.4, 777245315, 'https://assets.coingecko.com/coins/images/17984/thumb/9285.png?1630028620', 'https://assets.coingecko.com/coins/images/17984/small/9285.png?1630028620', 'https://assets.coingecko.com/coins/images/17984/large/9285.png?1630028620', 'movr', false, NULL),
(51, 'basic-attention-token', 'Basic Attention Token', 0.339972, 1500092844, 'https://assets.coingecko.com/coins/images/677/thumb/basic-attention-token.png?1547034427', 'https://assets.coingecko.com/coins/images/677/small/basic-attention-token.png?1547034427', 'https://assets.coingecko.com/coins/images/677/large/basic-attention-token.png?1547034427', 'bat', false, NULL),
(53, 'magic-internet-money', 'Magic Internet Money', 0.997032, 2366184395, 'https://assets.coingecko.com/coins/images/16786/thumb/mimlogopng.png?1624979612', 'https://assets.coingecko.com/coins/images/16786/small/mimlogopng.png?1624979612', 'https://assets.coingecko.com/coins/images/16786/large/mimlogopng.png?1624979612', 'mim', true, '#5955f8'),
(54, 'swissborg', 'SwissBorg', 0.163112, 594287602, 'https://assets.coingecko.com/coins/images/2117/thumb/YJUrRy7r_400x400.png?1589794215', 'https://assets.coingecko.com/coins/images/2117/small/YJUrRy7r_400x400.png?1589794215', 'https://assets.coingecko.com/coins/images/2117/large/YJUrRy7r_400x400.png?1589794215', 'chsb', false, NULL),
(55, 'instadapp', 'Instadapp', 0.694574, 78434259, 'https://assets.coingecko.com/coins/images/14688/thumb/30hFM0-n_400x400.jpg?1617786420', 'https://assets.coingecko.com/coins/images/14688/small/30hFM0-n_400x400.jpg?1617786420', 'https://assets.coingecko.com/coins/images/14688/large/30hFM0-n_400x400.jpg?1617786420', 'inst', false, NULL),
(56, 'asd', 'AscendEx Token', 0.080761, 376023899, 'https://assets.coingecko.com/coins/images/5003/thumb/bitmax.png?1621310871', 'https://assets.coingecko.com/coins/images/5003/small/bitmax.png?1621310871', 'https://assets.coingecko.com/coins/images/5003/large/bitmax.png?1621310871', 'asd', false, NULL),
(57, 'pancakeswap-token', 'PancakeSwap', 3.9, 4594097281, 'https://assets.coingecko.com/coins/images/12632/thumb/pancakeswap-cake-logo_%281%29.png?1629359065', 'https://assets.coingecko.com/coins/images/12632/small/pancakeswap-cake-logo_%281%29.png?1629359065', 'https://assets.coingecko.com/coins/images/12632/large/pancakeswap-cake-logo_%281%29.png?1629359065', 'cake', false, '#000000'),
(58, 'raydium', 'Raydium', 0.649816, 901740071, 'https://assets.coingecko.com/coins/images/13928/thumb/PSigc4ie_400x400.jpg?1612875614', 'https://assets.coingecko.com/coins/images/13928/small/PSigc4ie_400x400.jpg?1612875614', 'https://assets.coingecko.com/coins/images/13928/large/PSigc4ie_400x400.jpg?1612875614', 'ray', false, '#000000'),
(59, 'samoyedcoin', 'Samoyedcoin', 0.00988675, 524671448, 'https://assets.coingecko.com/coins/images/15051/thumb/IXeEj5e.png?1619560738', 'https://assets.coingecko.com/coins/images/15051/small/IXeEj5e.png?1619560738', 'https://assets.coingecko.com/coins/images/15051/large/IXeEj5e.png?1619560738', 'samo', false, '#000000'),
(60, 'orca', 'Orca', 0.781224, 174764445, 'https://assets.coingecko.com/coins/images/17547/thumb/Orca_Logo.png?1628781615', 'https://assets.coingecko.com/coins/images/17547/small/Orca_Logo.png?1628781615', 'https://assets.coingecko.com/coins/images/17547/large/Orca_Logo.png?1628781615', 'orca', false, '#000000'),
(61, 'only1', 'Only1', 0.01705897, 27293372, 'https://assets.coingecko.com/coins/images/17501/thumb/like-token.png?1628036165', 'https://assets.coingecko.com/coins/images/17501/small/like-token.png?1628036165', 'https://assets.coingecko.com/coins/images/17501/large/like-token.png?1628036165', 'like', false, '#000000'),
(62, 'solanium', 'Solanium', 0.103373, 127916636, 'https://assets.coingecko.com/coins/images/15816/thumb/token-icon.png?1621981387', 'https://assets.coingecko.com/coins/images/15816/small/token-icon.png?1621981387', 'https://assets.coingecko.com/coins/images/15816/large/token-icon.png?1621981387', 'slim', false, '#000000'),
(63, 'ftx-token', 'FTX Token', 26.41, 7111611909, 'https://assets.coingecko.com/coins/images/9026/thumb/F.png?1609051564', 'https://assets.coingecko.com/coins/images/9026/small/F.png?1609051564', 'https://assets.coingecko.com/coins/images/9026/large/F.png?1609051564', 'ftt', false, '#000000'),
(64, 'sora', 'Sora', 2.82, 102521696, 'https://assets.coingecko.com/coins/images/11093/thumb/sora_logo_cg_white.png?1588284194', 'https://assets.coingecko.com/coins/images/11093/small/sora_logo_cg_white.png?1588284194', 'https://assets.coingecko.com/coins/images/11093/large/sora_logo_cg_white.png?1588284194', 'xor', false, '#000000'),
(65, 'neutrino', 'Neutrino USD', 0.97957, 585339680, 'https://assets.coingecko.com/coins/images/10117/thumb/78GWcZu.png?1600845716', 'https://assets.coingecko.com/coins/images/10117/small/78GWcZu.png?1600845716', 'https://assets.coingecko.com/coins/images/10117/large/78GWcZu.png?1600845716', 'usdn', true, '#55f934'),
(66, 'shiden', 'Shiden Network', 0.278159, 201411890, 'https://assets.coingecko.com/coins/images/18027/thumb/tFOtjrr3_400x400.png?1630291767', 'https://assets.coingecko.com/coins/images/18027/small/tFOtjrr3_400x400.png?1630291767', 'https://assets.coingecko.com/coins/images/18027/large/tFOtjrr3_400x400.png?1630291767', 'sdn', false, '#000000'),
(67, 'human-protocol', 'HUMAN Protocol', 0.068404, 0, 'https://assets.coingecko.com/coins/images/16412/thumb/human_protocol.PNG?1623971316', 'https://assets.coingecko.com/coins/images/16412/small/human_protocol.PNG?1623971316', 'https://assets.coingecko.com/coins/images/16412/large/human_protocol.PNG?1623971316', 'hmt', false, '#000000'),
(68, 'immutable-x', 'Immutable X', 0.842292, 0, 'https://assets.coingecko.com/coins/images/17233/thumb/immutable-x.jpeg?1626921442', 'https://assets.coingecko.com/coins/images/17233/small/immutable-x.jpeg?1626921442', 'https://assets.coingecko.com/coins/images/17233/large/immutable-x.jpeg?1626921442', 'imx', false, '#000000'),
(69, 'karura', 'Karura', 0.46364, 129289604, 'https://assets.coingecko.com/coins/images/17172/thumb/karura.jpeg?1626782066', 'https://assets.coingecko.com/coins/images/17172/small/karura.jpeg?1626782066', 'https://assets.coingecko.com/coins/images/17172/large/karura.jpeg?1626782066', 'kar', false, '#000000'),
(70, 'mercurial', 'Mercurial', 0.01182773, 23188945, 'https://assets.coingecko.com/coins/images/15527/thumb/mer_logo.png?1621128922', 'https://assets.coingecko.com/coins/images/15527/small/mer_logo.png?1621128922', 'https://assets.coingecko.com/coins/images/15527/large/mer_logo.png?1621128922', 'mer', false, '#000000'),
(71, 'solarbeam', 'Solarbeam', 0.094212, 31205938, 'https://assets.coingecko.com/coins/images/18260/thumb/solarbeamlogo.png?1636080005', 'https://assets.coingecko.com/coins/images/18260/small/solarbeamlogo.png?1636080005', 'https://assets.coingecko.com/coins/images/18260/large/solarbeamlogo.png?1636080005', 'solar', false, '#000000'),
(72, 'oxygen', 'Oxygen', 0.051697, 466153960, 'https://assets.coingecko.com/coins/images/13509/thumb/8DjBZ79V_400x400.jpg?1609236331', 'https://assets.coingecko.com/coins/images/13509/small/8DjBZ79V_400x400.jpg?1609236331', 'https://assets.coingecko.com/coins/images/13509/large/8DjBZ79V_400x400.jpg?1609236331', 'oxy', false, '#000000'),
(73, 'maps', 'MAPS', 0.149478, 70463527, 'https://assets.coingecko.com/coins/images/13556/thumb/Copy_of_image_%28139%29.png?1609768934', 'https://assets.coingecko.com/coins/images/13556/small/Copy_of_image_%28139%29.png?1609768934', 'https://assets.coingecko.com/coins/images/13556/large/Copy_of_image_%28139%29.png?1609768934', 'maps', false, '#000000'),
(74, 'frax', 'Frax', 0.998434, 684356235, 'https://assets.coingecko.com/coins/images/13422/thumb/frax_logo.png?1608476506', 'https://assets.coingecko.com/coins/images/13422/small/frax_logo.png?1608476506', 'https://assets.coingecko.com/coins/images/13422/large/frax_logo.png?1608476506', 'frax', true, '#000000'),
(75, 'axie-infinity', 'Axie Infinity', 13.59, 10084166019, 'https://assets.coingecko.com/coins/images/13029/thumb/axie_infinity_logo.png?1604471082', 'https://assets.coingecko.com/coins/images/13029/small/axie_infinity_logo.png?1604471082', 'https://assets.coingecko.com/coins/images/13029/large/axie_infinity_logo.png?1604471082', 'axs', false, '#1100ff'),
(76, 'mimatic', 'MAI', 0.989561, 55736869, 'https://assets.coingecko.com/coins/images/15264/thumb/mimatic-red.png?1620281018', 'https://assets.coingecko.com/coins/images/15264/small/mimatic-red.png?1620281018', 'https://assets.coingecko.com/coins/images/15264/large/mimatic-red.png?1620281018', 'mimatic', true, '#db3737'),
(77, 'ripple', 'XRP', 0.327467, 58160001232, 'https://assets.coingecko.com/coins/images/44/thumb/xrp-symbol-white-128.png?1605778731', 'https://assets.coingecko.com/coins/images/44/small/xrp-symbol-white-128.png?1605778731', 'https://assets.coingecko.com/coins/images/44/large/xrp-symbol-white-128.png?1605778731', 'xrp', false, '#737373'),
(78, 'monero', 'Monero', 148.75, 4807608916, 'https://assets.coingecko.com/coins/images/69/thumb/monero_logo.png?1547033729', 'https://assets.coingecko.com/coins/images/69/small/monero_logo.png?1547033729', 'https://assets.coingecko.com/coins/images/69/large/monero_logo.png?1547033729', 'xmr', false, '#ff7b00'),
(79, 'stellar', 'Stellar', 0.103354, 9502987991, 'https://assets.coingecko.com/coins/images/100/thumb/Stellar_symbol_black_RGB.png?1552356157', 'https://assets.coingecko.com/coins/images/100/small/Stellar_symbol_black_RGB.png?1552356157', 'https://assets.coingecko.com/coins/images/100/large/Stellar_symbol_black_RGB.png?1552356157', 'xlm', false, '#000000'),
(80, 'paraswap', 'ParaSwap', 0.01811475, 96816178, 'https://assets.coingecko.com/coins/images/20403/thumb/ep7GqM19_400x400.jpg?1636979120', 'https://assets.coingecko.com/coins/images/20403/small/ep7GqM19_400x400.jpg?1636979120', 'https://assets.coingecko.com/coins/images/20403/large/ep7GqM19_400x400.jpg?1636979120', 'psp', false, '#2669f5'),
(81, 'vvs-finance', 'VVS Finance', 0.0000061, 63917200, 'https://assets.coingecko.com/coins/images/20210/thumb/8glAYOTM_400x400.jpg?1636667919', 'https://assets.coingecko.com/coins/images/20210/small/8glAYOTM_400x400.jpg?1636667919', 'https://assets.coingecko.com/coins/images/20210/large/8glAYOTM_400x400.jpg?1636667919', 'vvs', false, '#2c3a53'),
(82, 'celo-euro', 'Celo Euro', 0.989743, 49550199, 'https://assets.coingecko.com/coins/images/16756/thumb/CEUR.png?1624947266', 'https://assets.coingecko.com/coins/images/16756/small/CEUR.png?1624947266', 'https://assets.coingecko.com/coins/images/16756/large/CEUR.png?1624947266', 'ceur', true, '#000000'),
(83, 'maiar-dex', 'Maiar', 0.00005407, 0, 'https://assets.coingecko.com/coins/images/20657/thumb/MEX-icon.png?1637540149', 'https://assets.coingecko.com/coins/images/20657/small/MEX-icon.png?1637540149', 'https://assets.coingecko.com/coins/images/20657/large/MEX-icon.png?1637540149', 'mex', false, '#3555f7'),
(85, 'par-stablecoin', 'Parallel', 1.004, 2509846, 'https://assets.coingecko.com/coins/images/14153/thumb/par_round_200.png?1614670422', 'https://assets.coingecko.com/coins/images/14153/small/par_round_200.png?1614670422', 'https://assets.coingecko.com/coins/images/14153/large/par_round_200.png?1614670422', 'par', true, '#1f26ff'),
(86, 'stasis-eurs', 'STASIS EURO', 0.99621, 112585092, 'https://assets.coingecko.com/coins/images/5164/thumb/EURS_300x300.png?1550571779', 'https://assets.coingecko.com/coins/images/5164/small/EURS_300x300.png?1550571779', 'https://assets.coingecko.com/coins/images/5164/large/EURS_300x300.png?1550571779', 'eurs', true, '#b13dff'),
(87, 'tether-eurt', 'Euro Tether', 0.999764, 45383741, 'https://assets.coingecko.com/coins/images/17385/thumb/Tether_full_logo_dm.png?1627537298', 'https://assets.coingecko.com/coins/images/17385/small/Tether_full_logo_dm.png?1627537298', 'https://assets.coingecko.com/coins/images/17385/large/Tether_full_logo_dm.png?1627537298', 'eurt', true, '#00cc74'),
(88, 'defi-land', 'DeFi Land', 0.00190566, 86311686, 'https://assets.coingecko.com/coins/images/18910/thumb/defilend.png?1637190571', 'https://assets.coingecko.com/coins/images/18910/small/defilend.png?1637190571', 'https://assets.coingecko.com/coins/images/18910/large/defilend.png?1637190571', 'dfl', false, '#d6ba00'),
(89, 'solrazr', 'SolRazr', 0.054205, 0, 'https://assets.coingecko.com/coins/images/18390/thumb/Sol-Razr-Logo-TICKER.png?1631759669', 'https://assets.coingecko.com/coins/images/18390/small/Sol-Razr-Logo-TICKER.png?1631759669', 'https://assets.coingecko.com/coins/images/18390/large/Sol-Razr-Logo-TICKER.png?1631759669', 'solr', false, '#9b5be1'),
(90, 'genesysgo-shadow', 'GenesysGo Shadow', 0.223402, 0, 'https://assets.coingecko.com/coins/images/22271/thumb/logo_-_2022-01-05T083602.373.png?1641342974', 'https://assets.coingecko.com/coins/images/22271/small/logo_-_2022-01-05T083602.373.png?1641342974', 'https://assets.coingecko.com/coins/images/22271/large/logo_-_2022-01-05T083602.373.png?1641342974', 'shdw', false, '#4d8961'),
(91, 'qi-dao', 'Qi Dao', 0.157711, 21442442, 'https://assets.coingecko.com/coins/images/15329/thumb/qi.png?1620540969', 'https://assets.coingecko.com/coins/images/15329/small/qi.png?1620540969', 'https://assets.coingecko.com/coins/images/15329/large/qi.png?1620540969', 'qi', false, '#ff7a7a'),
(92, 'moonbeam', 'Moonbeam', 0.545367, 0, 'https://assets.coingecko.com/coins/images/22459/thumb/glmr.png?1641880985', 'https://assets.coingecko.com/coins/images/22459/small/glmr.png?1641880985', 'https://assets.coingecko.com/coins/images/22459/large/glmr.png?1641880985', 'glmr', false, '#54e3e1'),
(93, 'harmony', 'Harmony', 0.01995935, 3998085081, 'https://assets.coingecko.com/coins/images/4344/thumb/Y88JAze.png?1565065793', 'https://assets.coingecko.com/coins/images/4344/small/Y88JAze.png?1565065793', 'https://assets.coingecko.com/coins/images/4344/large/Y88JAze.png?1565065793', 'one', false, '#53c7a0'),
(94, 'refereum', 'Refereum', 0.00670461, 58736367, 'https://assets.coingecko.com/coins/images/2102/thumb/refereum.png?1548608001', 'https://assets.coingecko.com/coins/images/2102/small/refereum.png?1548608001', 'https://assets.coingecko.com/coins/images/2102/large/refereum.png?1548608001', 'rfr', false, '#ec2222'),
(95, 'tokemak', 'Tokemak', 1.24, 506385179, 'https://assets.coingecko.com/coins/images/17495/thumb/tokemak-avatar-200px-black.png?1628131614', 'https://assets.coingecko.com/coins/images/17495/small/tokemak-avatar-200px-black.png?1628131614', 'https://assets.coingecko.com/coins/images/17495/large/tokemak-avatar-200px-black.png?1628131614', 'toke', false, '#000000'),
(96, 'dopex', 'Dopex', 360.67, 581780916, 'https://assets.coingecko.com/coins/images/16652/thumb/DPX_%281%29.png?1624598630', 'https://assets.coingecko.com/coins/images/16652/small/DPX_%281%29.png?1624598630', 'https://assets.coingecko.com/coins/images/16652/large/DPX_%281%29.png?1624598630', 'dpx', false, '#001861'),
(97, 'near', 'Near', 3.93, 11558649272, 'https://assets.coingecko.com/coins/images/10365/thumb/near_icon.png?1601359077', 'https://assets.coingecko.com/coins/images/10365/small/near_icon.png?1601359077', 'https://assets.coingecko.com/coins/images/10365/large/near_icon.png?1601359077', 'near', false, '#000000'),
(99, 'metis-token', 'Metis Token', 31.41, 635098388, 'https://assets.coingecko.com/coins/images/15595/thumb/metis.PNG?1621298076', 'https://assets.coingecko.com/coins/images/15595/small/metis.PNG?1621298076', 'https://assets.coingecko.com/coins/images/15595/large/metis.PNG?1621298076', 'metis', false, '#00dacc'),
(100, 'netswap', 'Netswap', 0.306887, 39822157, 'https://assets.coingecko.com/coins/images/22262/thumb/netswpa.PNG?1641335781', 'https://assets.coingecko.com/coins/images/22262/small/netswpa.PNG?1641335781', 'https://assets.coingecko.com/coins/images/22262/large/netswpa.PNG?1641335781', 'nett', false, '#2179ff'),
(101, 'bonded-luna', 'Bonded Luna', 0.00116041, 0, 'https://assets.coingecko.com/coins/images/22369/thumb/17013.png?1641766740', 'https://assets.coingecko.com/coins/images/22369/small/17013.png?1641766740', 'https://assets.coingecko.com/coins/images/22369/large/17013.png?1641766740', 'bluna', false, '#f9d85e'),
(102, 'ref-finance', 'Ref Finance', 0.360818, 0, 'https://assets.coingecko.com/coins/images/18279/thumb/ref.png?1631238807', 'https://assets.coingecko.com/coins/images/18279/small/ref.png?1631238807', 'https://assets.coingecko.com/coins/images/18279/large/ref.png?1631238807', 'ref', false, '#000000'),
(103, 'cardano', 'Cardano', 0.44302, 38119661686, 'https://assets.coingecko.com/coins/images/975/thumb/cardano.png?1547034860', 'https://assets.coingecko.com/coins/images/975/small/cardano.png?1547034860', 'https://assets.coingecko.com/coins/images/975/large/cardano.png?1547034860', 'ada', false, '#236cd1'),
(104, 'hubble', 'Hubble', 0.189275, 0, 'https://assets.coingecko.com/coins/images/22070/thumb/hubble.PNG?1640749942', 'https://assets.coingecko.com/coins/images/22070/small/hubble.PNG?1640749942', 'https://assets.coingecko.com/coins/images/22070/large/hubble.PNG?1640749942', 'hbb', false, '#7884fe'),
(105, 'acala', 'Acala', 0.204373, 129986237, 'https://assets.coingecko.com/coins/images/20634/thumb/upOKBONH_400x400.jpg?1637366071', 'https://assets.coingecko.com/coins/images/20634/small/upOKBONH_400x400.jpg?1637366071', 'https://assets.coingecko.com/coins/images/20634/large/upOKBONH_400x400.jpg?1637366071', 'aca', false, '#000000'),
(106, 'defi-kingdoms', 'DeFi Kingdoms', 0.203132, 420386344, 'https://assets.coingecko.com/coins/images/18570/thumb/fAisLIV.png?1632449282', 'https://assets.coingecko.com/coins/images/18570/small/fAisLIV.png?1632449282', 'https://assets.coingecko.com/coins/images/18570/large/fAisLIV.png?1632449282', 'jewel', false, '#2ab059'),
(107, 'cardashift', 'Cardashift', 0, 0, 'https://assets.coingecko.com/coins/images/21618/thumb/tb87.png?1639611474', 'https://assets.coingecko.com/coins/images/21618/small/tb87.png?1639611474', 'https://assets.coingecko.com/coins/images/21618/large/tb87.png?1639611474', 'clap', false, '#5c73fd'),
(108, 'lp-3pool-curve', 'LP 3pool Curve', 1.022, 0, 'https://assets.coingecko.com/coins/images/12972/thumb/3pool_128.png?1603948039', 'https://assets.coingecko.com/coins/images/12972/small/3pool_128.png?1603948039', 'https://assets.coingecko.com/coins/images/12972/large/3pool_128.png?1603948039', '3crv', true, '#f82020'),
(109, 'usdh', 'USDH', 0.996428, 0, 'https://assets.coingecko.com/coins/images/22941/thumb/USDH_icon.png?1643008131', 'https://assets.coingecko.com/coins/images/22941/small/USDH_icon.png?1643008131', 'https://assets.coingecko.com/coins/images/22941/large/USDH_icon.png?1643008131', 'usdh', true, '#827cff'),
(110, 'juno-network', 'JUNO', 5.09, 1120498467, 'https://assets.coingecko.com/coins/images/19249/thumb/juno.png?1642838082', 'https://assets.coingecko.com/coins/images/19249/small/juno.png?1642838082', 'https://assets.coingecko.com/coins/images/19249/large/juno.png?1642838082', 'juno', false, '#000000'),
(111, 'osmosis', 'Osmosis', 1.11, 2566764933, 'https://assets.coingecko.com/coins/images/16724/thumb/osmo.png?1632763885', 'https://assets.coingecko.com/coins/images/16724/small/osmo.png?1632763885', 'https://assets.coingecko.com/coins/images/16724/large/osmo.png?1632763885', 'osmo', false, '#6c1ad1'),
(112, 'secret', 'Secret', 1.14, 1005387210, 'https://assets.coingecko.com/coins/images/11871/thumb/Secret.png?1595520186', 'https://assets.coingecko.com/coins/images/11871/small/Secret.png?1595520186', 'https://assets.coingecko.com/coins/images/11871/large/Secret.png?1595520186', 'scrt', false, '#000000'),
(113, 'iris-network', 'IRISnet', 0.01884775, 88471325, 'https://assets.coingecko.com/coins/images/5135/thumb/IRIS.png?1557999365', 'https://assets.coingecko.com/coins/images/5135/small/IRIS.png?1557999365', 'https://assets.coingecko.com/coins/images/5135/large/IRIS.png?1557999365', 'iris', false, '#9c3968'),
(114, 'astar', 'Astar', 0.03636629, 199286852, 'https://assets.coingecko.com/coins/images/22617/thumb/astr.png?1642314057', 'https://assets.coingecko.com/coins/images/22617/small/astr.png?1642314057', 'https://assets.coingecko.com/coins/images/22617/large/astr.png?1642314057', 'astr', false, '#016cd9'),
(115, 'umee', 'Umee', 0.01190801, 0, 'https://assets.coingecko.com/coins/images/20635/thumb/1Ab_Umee_Brand_Icon_Full_Color.png?1645018295', 'https://assets.coingecko.com/coins/images/20635/small/1Ab_Umee_Brand_Icon_Full_Color.png?1645018295', 'https://assets.coingecko.com/coins/images/20635/large/1Ab_Umee_Brand_Icon_Full_Color.png?1645018295', 'umee', false, '#6dedeb'),
(116, 'stargaze', 'Stargaze', 0.02935288, 0, 'https://assets.coingecko.com/coins/images/22363/thumb/stars.png?1645256657', 'https://assets.coingecko.com/coins/images/22363/small/stars.png?1645256657', 'https://assets.coingecko.com/coins/images/22363/large/stars.png?1645256657', 'stars', false, '#a99b1d'),
(117, 'stake-dao', 'Stake DAO', 0.470176, 33526198, 'https://assets.coingecko.com/coins/images/13724/thumb/stakedao_logo.jpg?1611195011', 'https://assets.coingecko.com/coins/images/13724/small/stakedao_logo.jpg?1611195011', 'https://assets.coingecko.com/coins/images/13724/large/stakedao_logo.jpg?1611195011', 'sdt', false, '#000000'),
(118, 'origin-dollar', 'Origin Dollar', 0.99784, 154115415, 'https://assets.coingecko.com/coins/images/12589/thumb/ousd-logo-200x200.png?1600943287', 'https://assets.coingecko.com/coins/images/12589/small/ousd-logo-200x200.png?1600943287', 'https://assets.coingecko.com/coins/images/12589/large/ousd-logo-200x200.png?1600943287', 'ousd', false, '#0081d1'),
(119, 'lido-staked-sol', 'Lido Staked SOL', 33, 214458274, 'https://assets.coingecko.com/coins/images/18369/thumb/logo_-_2021-09-15T100934.765.png?1631671781', 'https://assets.coingecko.com/coins/images/18369/small/logo_-_2021-09-15T100934.765.png?1631671781', 'https://assets.coingecko.com/coins/images/18369/large/logo_-_2021-09-15T100934.765.png?1631671781', 'stsol', false, '#22c0ff'),
(120, 'persistence', 'Persistence', 0.640848, 296218912, 'https://assets.coingecko.com/coins/images/14582/thumb/512_Light.png?1617149658', 'https://assets.coingecko.com/coins/images/14582/small/512_Light.png?1617149658', 'https://assets.coingecko.com/coins/images/14582/large/512_Light.png?1617149658', 'xprt', false, '#e50913'),
(121, 'sommelier', 'Sommelier', 0.290581, 0, 'https://assets.coingecko.com/coins/images/23308/thumb/sommelier.png?1643724224', 'https://assets.coingecko.com/coins/images/23308/small/sommelier.png?1643724224', 'https://assets.coingecko.com/coins/images/23308/large/sommelier.png?1643724224', 'somm', false, '#d3387c'),
(122, 'anchor-beth-token', 'Anchor bETH Token', 1809.71, 0, 'https://assets.coingecko.com/coins/images/21002/thumb/bETH.png?1638187691', 'https://assets.coingecko.com/coins/images/21002/small/bETH.png?1638187691', 'https://assets.coingecko.com/coins/images/21002/large/bETH.png?1638187691', 'beth', false, '#4bdb4b'),
(123, 'dopex-rebate-token', 'Dopex Rebate Token', 29.49, 191892385, 'https://assets.coingecko.com/coins/images/16659/thumb/rDPX_200x200_Coingecko.png?1624614475', 'https://assets.coingecko.com/coins/images/16659/small/rDPX_200x200_Coingecko.png?1624614475', 'https://assets.coingecko.com/coins/images/16659/large/rDPX_200x200_Coingecko.png?1624614475', 'rdpx', false, '#000000'),
(124, 'butterflydao', 'Redacted Cartel', 28.81, 187123287, 'https://assets.coingecko.com/coins/images/21718/thumb/3.png?1640248507', 'https://assets.coingecko.com/coins/images/21718/small/3.png?1640248507', 'https://assets.coingecko.com/coins/images/21718/large/3.png?1640248507', 'btrfly', false, '#000000'),
(125, 'graviton', 'Graviton', 0.02867381, 0, 'https://assets.coingecko.com/coins/images/24818/thumb/Graviton_-_Blue_200x200i.png?1649038479', 'https://assets.coingecko.com/coins/images/24818/small/Graviton_-_Blue_200x200i.png?1649038479', 'https://assets.coingecko.com/coins/images/24818/large/Graviton_-_Blue_200x200i.png?1649038479', 'grav', false, '#002fa7'),
(126, 'akash-network', 'Akash Network', 0.315407, 205880646, 'https://assets.coingecko.com/coins/images/12785/thumb/akash-logo.png?1615447676', 'https://assets.coingecko.com/coins/images/12785/small/akash-logo.png?1615447676', 'https://assets.coingecko.com/coins/images/12785/large/akash-logo.png?1615447676', 'akt', false, '#f34c2d'),
(127, 'evmos', 'Evmos', 2.3, 0, 'https://assets.coingecko.com/coins/images/24023/thumb/logo-orange.png?1651223278', 'https://assets.coingecko.com/coins/images/24023/small/logo-orange.png?1651223278', 'https://assets.coingecko.com/coins/images/24023/large/logo-orange.png?1651223278', 'evmos', false, '#cf5244'),
(128, 'junoswap-raw-dao', 'JunoSwap Raw Dao', 0, 0, 'https://assets.coingecko.com/coins/images/25277/thumb/Raw_Token_-_Outline.png?1651125561', 'https://assets.coingecko.com/coins/images/25277/small/Raw_Token_-_Outline.png?1651125561', 'https://assets.coingecko.com/coins/images/25277/large/Raw_Token_-_Outline.png?1651125561', 'raw', false, '#fa94b5'),
(129, 'binance-usd', 'Binance USD', 1.004, 18238218373, 'https://assets.coingecko.com/coins/images/9576/thumb/BUSD.png?1568947766', 'https://assets.coingecko.com/coins/images/9576/small/BUSD.png?1568947766', 'https://assets.coingecko.com/coins/images/9576/large/BUSD.png?1568947766', 'busd', true, '#f0b90b'),
(130, 'kava', 'Kava', 1.69, 493409671, 'https://assets.coingecko.com/coins/images/9761/thumb/kava.jpg?1639703080', 'https://assets.coingecko.com/coins/images/9761/small/kava.jpg?1639703080', 'https://assets.coingecko.com/coins/images/9761/large/kava.jpg?1639703080', 'kava', false, '#ff5651'),
(131, 'ideamarket', 'Ideamarket', 0.02591043, 0, 'https://assets.coingecko.com/coins/images/23330/thumb/6189656aa934fd0f709d1121_favilg.png?1643852444', 'https://assets.coingecko.com/coins/images/23330/small/6189656aa934fd0f709d1121_favilg.png?1643852444', 'https://assets.coingecko.com/coins/images/23330/large/6189656aa934fd0f709d1121_favilg.png?1643852444', 'imo', false, '#155dba'),
(132, 'magic', 'Magic', 0.546727, 34939328, 'https://assets.coingecko.com/coins/images/18623/thumb/Magic.png?1635755672', 'https://assets.coingecko.com/coins/images/18623/small/Magic.png?1635755672', 'https://assets.coingecko.com/coins/images/18623/large/Magic.png?1635755672', 'magic', false, '#f04646'),
(133, 'swapr', 'Swapr', 0.0251547, 1305229, 'https://assets.coingecko.com/coins/images/18740/thumb/swapr.jpg?1633516501', 'https://assets.coingecko.com/coins/images/18740/small/swapr.jpg?1633516501', 'https://assets.coingecko.com/coins/images/18740/large/swapr.jpg?1633516501', 'swpr', false, '#000000'),
(134, 'convex-crv', 'Convex CRV', 1.035, 279453250, 'https://assets.coingecko.com/coins/images/15586/thumb/convex-crv.png?1621255952', 'https://assets.coingecko.com/coins/images/15586/small/convex-crv.png?1621255952', 'https://assets.coingecko.com/coins/images/15586/large/convex-crv.png?1621255952', 'cvxcrv', false, '#eb0500'),
(135, 'terra-luna-2', 'Terra', 1.62, 0, 'https://assets.coingecko.com/coins/images/25767/thumb/01_Luna_color.png?1653556122', 'https://assets.coingecko.com/coins/images/25767/small/01_Luna_color.png?1653556122', 'https://assets.coingecko.com/coins/images/25767/large/01_Luna_color.png?1653556122', 'luna', false, '#e66940'),
(136, 'optimism', 'Optimism', 1.14, 0, 'https://assets.coingecko.com/coins/images/25244/thumb/OP.jpeg?1651026279', 'https://assets.coingecko.com/coins/images/25244/small/OP.jpeg?1651026279', 'https://assets.coingecko.com/coins/images/25244/large/OP.jpeg?1651026279', 'op', false, '#fc0423'),
(137, 'lifinity', 'Lifinity', 0.411911, 0, 'https://assets.coingecko.com/coins/images/25406/thumb/LFNTY_s.png?1651731251', 'https://assets.coingecko.com/coins/images/25406/small/LFNTY_s.png?1651731251', 'https://assets.coingecko.com/coins/images/25406/large/LFNTY_s.png?1651731251', 'lfnty', false, '#5754e2'),
(138, 'ageur', 'agEUR', 0.999845, 66296147, 'https://assets.coingecko.com/coins/images/19479/thumb/agEUR.png?1635283566', 'https://assets.coingecko.com/coins/images/19479/small/agEUR.png?1635283566', 'https://assets.coingecko.com/coins/images/19479/large/agEUR.png?1635283566', 'ageur', true, '#f4cca8'),
(139, 'staked-ether', 'Lido Staked Ether', 1445.9, 5003870108, 'https://assets.coingecko.com/coins/images/13442/thumb/steth_logo.png?1608607546', 'https://assets.coingecko.com/coins/images/13442/small/steth_logo.png?1608607546', 'https://assets.coingecko.com/coins/images/13442/large/steth_logo.png?1608607546', 'steth', false, '#00a3ff'),
(140, 'hop-protocol', 'Hop Protocol', 0.119834, 2446480, 'https://assets.coingecko.com/coins/images/25445/thumb/BVcNR51u_400x400.jpg?1651797443', 'https://assets.coingecko.com/coins/images/25445/small/BVcNR51u_400x400.jpg?1651797443', 'https://assets.coingecko.com/coins/images/25445/large/BVcNR51u_400x400.jpg?1651797443', 'hop', false, '#c950e3'),
(141, 'diffusion', 'Diffusion', 0.088251, 2000657, 'https://assets.coingecko.com/coins/images/25331/thumb/photo5451952870917257644.jpg?1651826321', 'https://assets.coingecko.com/coins/images/25331/small/photo5451952870917257644.jpg?1651826321', 'https://assets.coingecko.com/coins/images/25331/large/photo5451952870917257644.jpg?1651826321', 'diff', false, '#004d8b'),
(142, 'liquity-usd', 'Liquity USD', 1.016, 188460315, 'https://assets.coingecko.com/coins/images/14666/thumb/Group_3.png?1617631327', 'https://assets.coingecko.com/coins/images/14666/small/Group_3.png?1617631327', 'https://assets.coingecko.com/coins/images/14666/large/Group_3.png?1617631327', 'lusd', true, '#a676d5');




INSERT INTO public.blockchain (id, coin_id, libelle) VALUES
(1, 2, 'Ethereum'),
(2, 14, 'Polygon'),
(3, 4, 'Solana'),
(4, 22, 'Avalanche'),
(5, 26, 'Celo'),
(6, 44, 'Fantom'),
(7, 24, 'Terra'),
(8, 9, 'BSC'),
(10, 2, 'Arbitrum'),
(11, 2, 'Optimism'),
(12, 46, 'xDai'),
(13, 50, 'Moonriver'),
(14, 5, 'Polkadot'),
(15, 27, 'Kusama'),
(16, 23, 'Celsius'),
(17, 56, 'AscendEX'),
(18, 11, 'Cronos'),
(19, 15, 'Elrond'),
(20, 93, 'Harmony'),
(21, 99, 'Metis'),
(22, 110, 'Juno'),
(23, 111, 'Osmosis'),
(24, 25, 'Cosmos'),
(25, 112, 'Secret Network'),
(26, 103, 'Cardano'),
(27, 116, 'Stargaze'),
(28, 97, 'NEAR'),
(29, 130, 'Kava'),
(30, 127, 'Evmos'),
(31, 1, 'Bitcoin'),
(33, 30, 'Ultra');



INSERT INTO public.dapp (id, blockchain_id, libelle, url) VALUES
(1, 2, 'AAVE', 'https://app.aave.com/'),
(2, 2, 'Curve.fi', 'https://polygon.curve.fi/'),
(3, 10, 'Curve.fi', 'https://arbitrum.curve.fi/'),
(4, 2, 'Beefy Finance', 'https://app.beefy.finance/#/polygon'),
(5, 8, 'Beefy Finance', 'https://app.beefy.finance/#/bsc'),
(6, 4, 'Beefy Finance', 'https://app.beefy.finance/#/avax'),
(7, 6, 'Beefy Finance', 'https://app.beefy.finance/#/fantom'),
(8, 5, 'Beefy Finance', 'https://app.beefy.finance/#/celo'),
(9, 4, 'Snowball', 'https://app.snowball.network/'),
(10, 4, 'BENQI', 'https://app.benqi.fi/overview'),
(11, 4, 'TraderJoe', 'https://traderjoexyz.com/'),
(12, 4, 'Curve', 'https://avax.curve.fi/'),
(13, 4, 'Instadapp', 'https://avalanche.instadapp.io/'),
(14, 10, 'Instadapp', 'https://arbitrum.instadapp.io/'),
(15, 2, 'Instadapp', 'https://polygon.instadapp.io/'),
(16, 1, 'Instadapp', 'https://defi.instadapp.io/'),
(17, 2, 'QuickSwap', 'https://quickswap.exchange/'),
(18, 1, 'SushiSwap', 'https://app.sushi.com/swap'),
(19, 6, 'SushiSwap', 'https://app.sushi.com/swap'),
(20, 8, 'SushiSwap', 'https://app.sushi.com/swap'),
(21, 5, 'SushiSwap', 'https://app.sushi.com/swap'),
(22, 2, 'SushiSwap', 'https://app.sushi.com/'),
(23, 10, 'SushiSwap', 'https://app.sushi.com/'),
(24, 12, 'SushiSwap', 'https://app.sushi.com/'),
(25, 13, 'SushiSwap', 'https://app.sushi.com/'),
(26, 1, 'Stake DAO', 'https://app.stakedao.org/'),
(27, 2, 'Stake DAO', 'https://app.stakedao.org/'),
(28, 2, 'Adamant Finance', 'https://adamant.finance/'),
(29, 2, 'QiDao', 'https://app.mai.finance/'),
(30, 2, 'Balancer', 'https://polygon.balancer.fi/#/'),
(31, 1, 'Balancer', 'https://app.balancer.fi/#/'),
(32, 10, 'Balancer', 'https://arbitrum.balancer.fi/#/'),
(33, 2, 'IRON Finance', 'https://app.iron.finance/'),
(34, 6, 'IRON Finance', 'https://app.iron.finance/'),
(35, 4, 'IRON Finance', 'https://app.iron.finance/'),
(36, 3, 'Mercurial Finance', 'https://mercurial.finance/'),
(37, 3, 'Tulip Garden', 'https://tulip.garden/'),
(38, 3, 'Sunny Aggregator', 'https://app.sunny.ag/'),
(39, 3, 'Solend', 'https://solend.fi/'),
(40, 3, 'Oxygen', 'https://app.oxygen.org/'),
(41, 8, 'Pancake Swap', 'https://pancakeswap.finance/'),
(42, 1, 'Uniswap', 'https://app.uniswap.org/#/swap'),
(43, 10, 'Uniswap', 'https://app.uniswap.org/#/swap'),
(44, 7, 'Anchor', 'https://app.anchorprotocol.com/'),
(45, 7, 'Mirror', 'https://mirror.finance/'),
(46, 5, 'Mobius', 'https://www.mobius.money/#/swap'),
(47, 16, 'Celsius', 'https://app.celsius.network/'),
(48, 17, 'AscendEX', 'https://ascendex.com/'),
(49, 13, 'Solarbeam', 'https://app.solarbeam.io/'),
(50, 13, 'Beefy Finance', 'https://app.beefy.finance/#/moonriver'),
(51, 13, 'SushiSwap', 'https://app.sushi.com/'),
(52, 3, 'Aurory', 'https://app.aurory.io/'),
(53, 18, 'VVS Finance', 'vvs.finance'),
(54, 18, 'Beefy Finance', 'https://app.beefy.finance/#/cronos'),
(55, 19, 'Maiar Exchange', 'https://maiar.exchange/'),
(56, 4, 'QiDao', 'https://app.mai.finance/'),
(57, 2, 'Jarvis Network', 'yield.jarvis.network'),
(58, 1, 'Paraswap', 'https://paraswap.io/'),
(59, 7, 'Terra Station', 'https://station.terra.money/'),
(60, 3, 'DeFi Land', 'https://play.defiland.app/'),
(61, 4, 'Stake DAO', 'https://app.stakedao.org/'),
(62, 2, 'PoolTogether', 'https://v4.pooltogether.com/'),
(63, 4, 'PoolTogether', 'https://v4.pooltogether.com/'),
(64, 1, 'PoolTogether', 'https://v4.pooltogether.com/'),
(65, 6, 'YearnFinance', 'https://yearn.finance/'),
(66, 1, 'YearnFinance', 'https://yearn.finance/'),
(67, 3, 'Francium', 'https://francium.io/app'),
(68, 6, 'Tetu', 'https://app.tetu.io/'),
(69, 20, 'Stake DAO', 'https://app.stakedao.org/'),
(70, 20, 'Tranquil', 'https://app.tranquil.finance/'),
(71, 6, 'Tarot', 'https://www.tarot.to/'),
(72, 21, 'Beefy Finance', 'https://app.beefy.finance/#/metis'),
(73, 21, 'Netswap', 'https://netswap.io/'),
(74, 20, 'Defi Kingdoms', 'https://game.defikingdoms.com/#/'),
(75, 6, 'QiDao', 'https://app.mai.finance/'),
(76, 6, 'SpookySwap', 'https://spookyswap.finance/'),
(77, 3, 'Hubble Protocol', 'https://app.hubbleprotocol.io/'),
(78, 22, 'Junoswap', 'https://junoswap.com/'),
(79, 20, 'Beefy Finance', 'https://app.beefy.finance/#/harmony'),
(80, 24, 'Keplr Dashboard', 'https://wallet.keplr.app/#/'),
(81, 19, 'Maiar App', 'https://maiar.com/'),
(82, 23, 'Osmosis', 'https://app.osmosis.zone/'),
(83, 10, 'Impermax', 'https://arbitrum.impermax.finance/'),
(84, 2, 'Impermax', 'https://polygon.impermax.finance/'),
(85, 1, 'Impermax', 'https://app.impermax.finance/'),
(86, 4, 'Impermax', 'https://avalanche.impermax.finance/'),
(87, 6, 'Impermax', 'https://fantom.impermax.finance/'),
(88, 21, 'Hermes', 'https://hermes.maiadao.io/'),
(89, 21, 'Starstream', 'https://starstream.finance/'),
(90, 10, 'QiDao', 'https://app.mai.finance/'),
(91, 10, 'Balancer', 'https://arbitrum.balancer.fi/'),
(92, 26, 'Minswap', 'https://app.minswap.org/'),
(93, 26, 'SundaeSwap', 'https://exchange.sundaeswap.finance/'),
(94, 7, 'Stader', 'https://terra.staderlabs.com/'),
(95, 2, 'Stargate Finance', 'https://stargate.finance/'),
(96, 8, 'Stargate Finance', 'https://stargate.finance/'),
(97, 6, 'Stargate Finance', 'https://stargate.finance/'),
(98, 10, 'Stargate Finance', 'https://stargate.finance/'),
(99, 11, 'Stargate Finance', 'https://stargate.finance/'),
(100, 6, 'AAVE', 'https://app.aave.com/'),
(101, 10, 'AAVE', 'https://app.aave.com/'),
(102, 11, 'AAVE', 'https://app.aave.com/'),
(103, 4, 'AAVE', 'https://app.aave.com/'),
(104, 20, 'AAVE', 'https://app.aave.com/'),
(105, 1, 'AAVE', 'https://app.aave.com/'),
(106, 2, 'Arrakis', 'https://arrakis.finance/'),
(107, 12, 'RealT RMM', 'https://rmm.realt.network/'),
(108, 6, 'Market.xyz', 'https://fantom.market.xyz/'),
(109, 11, 'Rubicon', 'https://app.rubicon.finance/'),
(110, 11, 'Pika', 'https://app.pikaprotocol.com/'),
(111, 11, 'Clipper', 'https://clipper.exchange/'),
(112, 2, 'Nested', 'https://app.nested.fi/'),
(113, 1, 'Nested', 'https://app.nested.fi/'),
(114, 4, 'Nested', 'https://app.nested.fi/'),
(115, 8, 'Nested', 'https://app.nested.fi/'),
(116, 11, 'Nested', 'https://app.nested.fi/'),
(117, 6, 'Nested', 'https://app.nested.fi/'),
(118, 10, 'Nested', 'https://app.nested.fi/'),
(119, 5, 'Nested', 'https://app.nested.fi/'),
(120, 11, 'QiDao', 'https://app.mai.finance/'),
(121, 6, 'BeethovenX', 'https://beets.fi/#/'),
(122, 23, 'Keplr Dashboard', 'https://wallet.keplr.app/#/dashboard'),
(123, 2, 'Gotchi Vault', 'https://www.gotchivault.com/vqi'),
(124, 22, 'Raw DAO', 'https://www.rawdao.zone/'),
(125, 22, 'Keplr Dashboard', 'https://wallet.keplr.app/#/juno/stake'),
(126, 29, 'Kava', 'https://app.kava.io/'),
(127, 30, 'Keplr Dashboard', 'https://wallet.keplr.app/#/evmos/stake'),
(128, 30, 'Diffusion', 'https://app.diffusion.fi/#/swap'),
(129, 1, 'Stake DAO Liquid Lockers', 'https://lockers.stakedao.org/'),
(130, 31, 'Lightning Network', 'http://umbrel.local/lightning'),
(131, 1, 'Convex', 'https://www.convexfinance.com/stake'),
(132, 1, 'Lido', 'https://stake.lido.fi/'),
(133, 12, 'QiDao', 'https://app.mai.finance/'),
(134, 12, 'Curve', 'https://xdai.curve.fi/'),
(135, 10, 'Beefy Finance', 'https://app.beefy.finance/'),
(136, 4, 'Autofarm', 'https://autofarm.network/avax/'),
(137, 10, 'Vovo Finance', 'https://vovo.finance/'),
(138, 2, 'Overnight', 'https://app.overnight.fi/'),
(139, 2, 'Dystopia', 'https://www.dystopia.exchange/'),
(140, 2, 'Swaap', 'swaap.finance'),
(141, 11, 'Beefy Finance', 'https://app.beefy.finance/');


INSERT INTO public.deposit_type (id, libelle) VALUES
(1, 'Carte bleu'),
(2, 'Virement bancaire');


INSERT INTO public.exchange (id, libelle, url) VALUES
(1, 'Binance', 'https://www.binance.com/fr'),
(2, 'Coinbase', 'https://www.coinbase.com/fr/'),
(3, 'FTX', 'https://ftx.com/'),
(4, 'Swissborg', 'https://swissborg.com/fr/'),
(5, 'AsxcendEX', 'https://ascendex.com/'),
(6, 'Kraken', 'https://www.kraken.com/'),
(7, 'Jarvis', 'https://jarvis.network/'),
(8, 'Crypto.com', 'https://crypto.com/');


INSERT INTO public.type_project (id, libelle) VALUES
(1, 'Blockchain'),
(2, 'DAO'),
(3, 'Oracle'),
(4, 'Gaming'),
(5, 'Privacy'),
(6, 'Sport'),
(7, 'Exchange'),
(8, 'NFT'),
(9, 'Autres');






































































































































































































































