This is a project with a single microservice, for testing purposes.



Install the project steps:
1. After you grab the project files, you will run the composer install -o
2. You can run the Symfony's web server using: symfony server:start
3. You will have access to the App on this page: http://127.0.0.1:8000
4. Run the command to create the containers in Docker (MySQL and Redis): docker-compose up -d
5. Run the migration file to create the SQL Schema: symfony console doctrine:migrations:migrate
6. Run the command to add some test data in the Database: symfony console doctrine:fixtures:load
7. To test the request/response use the url: http://127.0.0.1:8000/products/{{product_id}}/lowest-price
8. Send a POST request from Postman with this JSON (change the {{product_id}} with the product id you have in the database or set an environment variable in Postman):
```
    {
    "quantity": 5,
    "request_location": "RO",
    "voucher_code": "2w3e4r",
    "request_date": "2023-12-25",
    "product_id": {{product_id}}
}
```
9. The Unit tests are run with: vendor/bin/phpunit tests/unit/



Steps to develop this from scratch:
1. We setup the project on GitHub
2. Promotions Service App Setup:
    - I installed it as a Symfony Skeleton, phpunit and maker with composer
3. Routing:
    - create the routes for the microservice, we currently use one
4. Data Transfer Object:
    - Create a directory called DTO.
    - Inside create class named LowestPriceEnquiry and an interface named PromotionEnquiryInterface.
    - We install the serializer with composer: composer require serializer
    - We tell ProductsController to extend AbstractController to be able to have the Serializer autowired.
    - Deserialize JSON data into an EnquiryDTO Object.
    - The way we currently test the request and the response is by using Postman. We send a request with a JSON object and we receive a LowestPriceEnquiry DTO object in the response.
5. Custom Serializer:
    - A custom serializer is the solution to this problem: we need to return the same snake case JSON format as received in the request, but the Symfony Serializer is returning the data in camel case format.
    - We create a Service directory and inside this a Serializer directory.
    - A new serializer class will be created named DTOSerializer which extends the Symfony's SerializerInterface.
    - In the DTOSerializer we implement the methods from SerializerInterface: serialize() and deserialize(), but we don't change any of the serialize() or deserialize() implementation. We will just copy them from the SerializerInterface.
    - In the DTOSerializer constructor we will instantiate a new Symfony\Component\Serializer\Serializer object which will create arrays with new instances of ObjectNormalizer respectively JsonEncoder
    - We will use the DTOSerializer as the 3rd parameter of lowestPrice() method in ProductsController.
    - We will deserialize and serialize the enquiry JSON
6. Promotions Filter:
    - We create a directory named Filter.
    - Inside Filter we create a PromotionsFilterInterface and a LowestPriceFilter class which implements the PromotionsFilterInterface.
    - The PromotionsFilterInterface has a method apply() which receives and returns a PromotionEnquiryInterface.
    - LowestPriceFilter implements PromotionsFilterInterface and when it implements the method apply() it assigns some values to the $enquiry object.
    - Finally the PromotionsFilterInterface is added into the ProductsController as a parameter of lowestPrice() method which is autowired. This allows us to use this $promotionsFilter, apply this filter to the $lowestPriceEnquiry and return the $modifiedEnquiry as JSON.
7. Schema:
    - We need to add promotions to products and the best way to do this is to have a "Product" table and a "Promotion" table with a relationship table between them. The relationship table is named "ProductPromotion"
    - We create the following schema:
    "Product: id (int), price (int)
    "ProductPromotion": id (int), product_id (int), promotion_id (int), valid_to (datetime)
    "Promotion: id (int), name (string), type (string), adjustment (float), criteria (string|json)
    - We run the command to install Doctrine: composer require doctrine (if for some reason you get an error that says some components are not on the right version run the command like this: composer require doctrine --with-all-dependencies)
    - We will use the command "bin/console make:entity" to create the following entities classes: Product, ProductPromotion and Promotion
    - We verify if the entities are created with the expected properties 
8. Docker MySQL Integration:
    - We run the command: bin/console make:docker:database
    - Check the new "docker-compose.yaml" file created by using the previous command
    - We modify the "docker-compose.yaml" file and add the following lines:
    ```
    volumes:
            - ./mysql:/var/lib/mysql
    ```
    This creates maps the database from Docker to a local mysql file (./mysql) to ensure that the database doesn't get deleted when we rebuild Docker
    - We should have the Docker desktop installed on local environment
    - We can run the command to create the Docker MySQL database: docker-compose up -d
    - We add the newly created mysql directory to .gitignore to not commit this directory
    - We run the command to see the variables stored in Symfony (install the symfony cli from https://symfony.com/download to have this command available):
    ```
    symfony var:export --multiline
    ```
    We keep in mind that the "DATABASE_URL" defined in the .env file will be replaced by the environment variable we see in the command output from above
    - We run the command: 
    ```
    symfony console make:migration
    ```
    By using the "symfony" we ensure the command is run on the Docker's database
    - We will check the newly created "migration" file
    - We run the command to run the SQL statements from migrations to the database: symfony console doctrine:migrations:migrate
    - To check the database tables we copy the Port from the above "DATABASE_URL" variable and we connect to the database
    - We also edit the docker-compose.yaml to setup the local port with the container's port (this ensures a port mapping that works after each container restart)
9. Repositories and Query Builder:
    - We'll add some data to the database by using Doctrine Fixtures.
    - Run the command: composer require orm-fixtures --dev
    - This command will create a DataFixtures in src which has a class AppFixtures.php
    - After we add all the fixtures in the AppFixtures.php we run the command to empty and populate the database: symfony console doctrine:fixtures:load
    - We add a Product property to the LowestPriceEnquiry DTO object.
    - In the ProductsController we inject the ProductRepository in the constructor to query for the product.
    - We query for the product and set it into the $lowestPriceEnquiry.
    - We also query for promotions by using this newly created method findValidForProduct()
    - We then use these promotions in ProductsController here:
    ```
    $modifiedEnquiry = $promotionsFilter->apply($lowestPriceEnquiry, $promotions);
    ```
10. Type-safe Arrays of Objects:
    - the aim here is to make sure the promotions we get from the Database is exactly an array of promotions
    - we currently deleted the properties and methods from the Product entity that have a reference to Promotion because: we don't need them for now and we had a Circular Reference error from Symfony and Serializer (one other way to fix this is by using groups probably, but is not the scope of this now)
    - at this moment, the response returns a JSON with a modified Enquiry, but we receive the serialized Product too which we don't need. To fix this we add the annotation in class LowestPriceEnquiry on the $product property:
    ```
    #[Ignore]
    ```
    - Because the App doesn't read the Annotation on the Product yet, we need one more step: instruct the DTOSerializer to read the class metadata we added. This is obtained by adding a named argument to the ObjectNormalizer from the DTOSerializer's construct. The named argument is classMetadataFactory and we instruct it to read the annotations by giving it AnnotationLoader and AnnotationReader
11. Test Setup:
    - as of now we use some hardcoded data and Postman to call the LowestPriceFilter's apply() method, therefore we will create tests
    - because this is a service which makes HTTP requests most probably we want to mimic this. We will create a LowestPriceFilterTest class which has the ability to use the Symfony's Container to make requests.
    - in the Unit Test we want to assert the price, discountedPrice and promotionName are the same
12. Driving out Dependencies
13. Price Modifiers:
    - We will create a PriceModifiersTest where we will test the DateRangeMultiplier calculations
    - We then create a PriceModifierInterface and a DateRangeMultiplier class which implements PriceModifierInterface method
    - Similar to the above logic we will also create a test for FixedPriceVoucher class
14. EvenItemsMultiplier calculation:
    - We will create the last EvenItemsMultiplier class and the test for it and we implement a calculation for it
15. Create a Factory Class:
    - We want to create a Price Modifier so we will use the Factory Pattern.
    - We will create a PriceModifierFactoryInterface and a PriceModifierFactory.
    - The PriceModifierFactory will create instances of PriceModifierInterface subclasses based on the Promotion modifier type
16. A Working Product:
    - We make sure the LowestPriceFilter's apply() method works with any array of promotions and all the tests are passing
    - We added a change in the Promotion class and we need to identify why is not working without it:
    ```
        private ?int $id = null;
    ```
17. Overriding parent methods:
18. Interface Segregation:
    - implement the Inteface Segregation Principle:
        - create a new PriceEnquiryInterface which extends PromotionEnquiryInterface and with 3 new methods which relate to Price Enquiries
        - create a PriceFilterInterface which extends PromotionsFilterInterface and move the apply() method from the PromotionsFilterInterface to the PriceFilterInterface
        - LowestPriceEnquiry implements PriceEnquiryInterface instead of PromotionEnquiryInterface
        - PromotionEnquiryInterface gets 3 new methods which relate to Promotion Enquiries
        - LowestPriceFilter implements PriceFilterInterface instead of PromotionsFilterInterface and for the apply() method we change the 1st parameter to PriceEnquiryInterface and the return type to PriceEnquiryInterface as well
19. Caching (Symfony's file caching system):
    - Run the command: composer require cache
    - This will install the Symfony's cache which we can use in the Controller, but as a better design solution, instead of adding this cache logic into the Contoller, we can create a custom Cache solution for Promotions that the Controller can use
    - We create a PromotionCache for caching promotions and we use the method in the ProductController
    - We can test the Cache deletion with command: symfony console cache:pool:delete cache.app valid-for-product-{product_id}
    - The cache.app from above represents the cache pool
20. Caching with Redis:
    - run the command to remove the existing container: docker-compose down
    - modify the docker-compose.yaml to add the Redis image
    - modify the cache.yaml file and add the Redis Url from the Symfony environment
    - run the command to install the package with the library that helps PHP work with Redis: composer require predis/predis
    - run the command: docker-compose up -d
21. Event Subscribers and Validation:
    - we can use 2 type of event listeners:
        - the Symfony Event Listeners which is the most loosly coupled system: create an event and have listeners that require some configuration that tell the listeners to listen for an event
        - use the Event Subscribers which is a tightly coupled system that tell the Event Subscribers which events they are listening too, you code into the Event Subscriber class (we'll use this)
    - Create an AfterDtoCreatedEvent class in the directory Event
    - Create an DtoSubscriber event class with the method to validate the DTO
    - We run the command to install the validator component: composer require validator
    - We use the Validator inside the DtoSubscriber
    - We want to test the validation from the DtoSubscriber and we create an Unit test named DtoSubscriberTest
    - Add constraints on some of the LowestPriceEnquiry's properties which will be used by the Validator
    - Inside the DTOSerializer's deserialize() method create the event and dispatch it after the DTO is deserialized
    - The tests should pass at this point
22. Listening for Exceptions & Exception Response Content Body & Non custom Exception Handling:
    - We will create an Exception listener that will listen to exceptions being thrown and we will throw our handling
    - We create a class ExceptionListener inside the EventListener. It has a method onKernelException.
    - We add a new service in services.yaml file with a tag that says: "the kernel.event_listener listens for kernel.exception"
    - We create the ServiceException and ServiceExceptionData class in the Service directory
    - We create the ValidationExceptionData as well
    - We update the DtoSubscriber and DtoSubscriberTest to use the new Validation Exceptions