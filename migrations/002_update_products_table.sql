SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `products`;
ALTER TABLE `products` MODIFY COLUMN `category` ENUM('coffee', 'tea', 'chocolate', 'refreshment', 'signature', 'addon') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'coffee';

INSERT INTO `products` (`name`, `ingredients`, `quantity`, `price`, `category`, `image_path`, `description`) VALUES
('Espresso single', 'cafea, apă', '20ml', 10.00, 'coffee', 'assets/menu/images/espresso.webp', 'Esența pură a cafelei: un shot intens, bogat și plin de caracter, extras cu măiestrie pentru a trezi toate simțurile.'),
('Espresso dublu', 'cafea, apă', '40ml', 12.00, 'coffee', 'assets/menu/images/TBD.jpg', 'Energie la dublu. Aceeași intensitate remarcabilă, într-o doză generoasă care îți oferă impulsul perfect pentru întreaga zi.'),
('Long Black', 'espresso dublu, apă', '80ml', 12.00, 'coffee', 'assets/menu/images/TBD.jpg', 'Eleganță în simplitate. Un dublu espresso turnat delicat peste apă fierbinte, păstrând crema bogată și aromele intense.'),
('V60', 'cafea, apă', '200ml', 20.00, 'coffee', 'assets/menu/images/TBD.jpg', 'O experiență artizanală. Cafea preparată manual, picătură cu picătură, pentru a dezvălui notele florale și fructate subtile ale boabelor de origine.'),
('Cortado', 'espresso single, cremă de lapte', '120ml', 12.00, 'coffee', 'assets/menu/images/cortado.jpeg', 'Echilibrul ideal. Tăria unui espresso întâlnește dulceața laptelui cald, într-o armonie perfectă de gust și textură.'),
('Cappuccino', 'espresso single, cremă de lapte', '200ml', 14.00, 'coffee', 'assets/menu/images/cappuccino.jpg', 'Clasicul italian desăvârșit. Espresso catifelat îmbrățișat de lapte cald și o coroană bogată de spumă fină.'),
('Flat White', 'espresso dublu, cremă de lapte', '180ml', 15.00, 'coffee', 'assets/menu/images/TBD.jpg', 'Cremozitate absolută. Două shot-uri de espresso învăluite în cremă de lapte micro-texturată, pentru o băutură fină, dar puternică.'),
('Latte', 'espresso single, cremă de lapte', '300ml', 16.00, 'coffee', 'assets/menu/images/latte.webp', 'Răsfăț cremos. O îmbrățișare caldă de lapte spumat și espresso, perfectă pentru momentele lungi de relaxare.'),
('Babyccino', 'cremă de lapte', '120ml', 6.00, 'chocolate', 'assets/menu/images/babycino.jpg', 'Bucuria celor mici. Spumă de lapte pufoasă, pudrată cu cacao fină – un deliciu jucăuș fără cofeină.'),
('Hot Cioco', 'ciocolată caldă pudră, cremă de lapte', '200ml', 15.00, 'chocolate', 'assets/menu/images/hot_chocolate.jpg', 'Decadență lichidă. Ciocolată caldă premium, densă și catifelată, care îți încălzește sufletul cu fiecare înghițitură.'),
('Ceai', 'ceai, apă', '300ml', 14.00, 'tea', 'assets/menu/images/ceai.jpg', 'Infuzia liniștii. O selecție de frunze de ceai premium, alese cu grijă pentru a oferi o pauză de prospețime și calm.'),
('Espresso Tonic', 'espresso dublu, apă tonică, gheață', '180ml', 17.00, 'signature', 'assets/menu/images/TBD.jpg', 'Efervescență și energie. Întâlnirea surprinzătoare dintre espresso intens și apa tonică rece, pentru un cocktail de cafea revitalizant.'),
('Cold Brew Tonic', 'cold brew, apă tonică, gheață', '180ml', 17.00, 'signature', 'assets/menu/images/cold_brew_tonic.jpg', 'Răcorire sofisticată. Finețea cafelei cold brew combinată cu perlajul apei tonice, o băutură vibrantă și cristalină.'),
('Cold Brew Latte', 'cold brew, apă, gheață', '250ml', 16.00, 'coffee', 'assets/menu/images/cold_brew_latte.webp', 'Dulceață naturală. Cafea extrasă la rece și lapte proaspăt, o alternativă fină, lipsită de aciditate, perfectă pentru zilele calde.'),
('Cold Brew', 'cafea, apă, gheață', '180ml', 14.00, 'coffee', 'assets/menu/images/cold_brew.jpg', 'Răbdarea gustului. Cafea infuzată lent în apă rece timp îndelungat, rezultând un elixir dulceag, energizant și plin de claritate.'),
('Ice Cappuccino', 'espresso single, cremă de lapte, gheață', '180ml', 14.00, 'coffee', 'assets/menu/images/TBD.jpg', 'Cappuccino-ul tău preferat, servit „on the rocks”. Espresso rece, lapte și spumă, pentru o plăcere răcoroasă.'),
('Ice Latte', 'espresso single, cremă de lapte, gheață', '250ml', 16.00, 'coffee', 'assets/menu/images/ice_latte.jfif', 'Clasicul Latte în variantă estivală. Espresso rece turnat peste lapte și gheață, simplu și revigorant.'),
('Matcha Latte', 'ceai matcha pudră, apă, cremă de lapte', '300ml', 20.00, 'tea', 'assets/menu/images/TBD.jpg', 'Ritualul verde. Pudră fină de ceai verde Matcha japonez, bogată în antioxidanți, combinată cu lapte cremos pentru o energie zen.'),
('Ice Matcha Latte', 'ceai matcha pudră, apă, cremă de lapte, gheață', '250ml', 20.00, 'tea', 'assets/menu/images/TBD.jpg', 'Zen în pahar de gheață. Prospețimea vegetală a ceaiului Matcha întâlnește răcoarea laptelui cu gheață.'),
('Matcha Tonic', 'ceai matcha pudră, apă, apă tonică', '180ml', 22.00, 'signature', 'assets/menu/images/TBD.jpg', 'Energie efervescentă. O băutură modernă și vibrantă, unde Matcha întâlnește apa tonică pentru un boost de vitalitate.'),
('Socată / Limonadă cu soc', 'sirop de soc, zeamă de lămâie, apă carbogazoasă, gheață', '250ml', 14.00, 'refreshment', 'assets/menu/images/TBD.jpg', 'Gustul copilăriei reinterpretat. O băutură artizanală răcoritoare, cu arome florale de soc și lămâie proaspătă.'),
('Limonadă cu zmeură', 'sirop de zmeură, zeamă de lămâie, apă carbogazoasă', '250ml', 14.00, 'refreshment', 'assets/menu/images/TBD.jpg', 'Explozie fructată. Limonadă clasică îmbogățită cu sirop natural de zmeură, dulce-acrișoară și irezistibilă.'),
('Extra shot', 'espresso single', '20ml', 3.00, 'addon', 'assets/menu/images/TBD.jpg', 'Boost-ul tău de energie. Adaugă încă o doză de intensitate băuturii tale preferate.'),
('Lapte vegetal', 'lapte de ovăz/mazăre', NULL, 3.00, 'addon', 'assets/menu/images/TBD.jpg', 'Alternative delicioase. Optează pentru lapte de ovăz sau mazăre, cremos și prietenos cu natura, pentru cafeaua ta.');
SET FOREIGN_KEY_CHECKS = 1;
