Simple example API project using some CQRS/Tactical DDD concepts. Intentionally avoided use of Laravel or heavy frameworks, as not to overcomplicate. It's certainly overkill to add so much separation for 3 simple API calls! However, does one not try to impress in examples?

Definitely went overboard in a few spots. Probably didn't need to worry about storing/hydrating the objects.

Has 3 API routes:
- GET /api/sellers [src/Seller/Infrastructure/Api/Rest/ApiController.php#L51](https://github.com/erinlessard/example-without-laravel/blob/main/src/Seller/Infrastructure/Api/Rest/ApiController.php#L51) - Return a list of sellers
- GET /api/sellers/{seller_id} [src/Seller/Infrastructure/Api/Rest/ApiController.php#L27](https://github.com/erinlessard/example-without-laravel/blob/main/src/Seller/Infrastructure/Api/Rest/ApiController.php#L27) - Return a single seller
- POST /api/sellers [src/Seller/Infrastructure/Api/Rest/ApiController.php#L71](https://github.com/erinlessard/example-without-laravel/blob/main/src/Seller/Infrastructure/Api/Rest/ApiController.php#L71) - Create a new seller

## Notes and thoughts
- Included DI container. Didn't really need to, initially was going to add a Sqlite implementation of the StorageInterface but wanted to keep the example quick.
- Sorta followed a quick, messy DDD approach where most of the domain logic around property validation is on the Seller entity.
- I decided to try Slim for routing / dependency injection as I'd never used it and was curious. I felt like it'd be overkill to bring Laravel in here.
  - (see [src/bootstrap.php](https://github.com/erinlessard/example-without-laravel/blob/main/src/bootstrap.php))
  - Slim makes use of PSR-7 for HTTP messages (I like PSR standards)
- Query handlers return DTOs that are ready to be json encoded into a response (Laravel would handle this automatically when returning the DTO, for example)
- Tests for Controller, Handlers and Domain object.
- Made use of PSR-3 interface in controller but didn't add specific library. Monolog is the go-to option, plus your favorite APM/error tracker (Sentry, New Relic, Datadog etc)
- [src/Seller/Application](https://github.com/erinlessard/example-without-laravel/tree/main/src/Seller/Application) 
    - Split up into Command/Query objects and handlers in the Application layer, following some DDD/CQRS principles.
    - The query aspect can get a bit verbose and many would consider them unnecessary, particularly since they're not offering any value atm (I don't disagree).
    - Reorganized folder structure as appropriate
    - Mostly just to show the concepts.

## Notes about specific files

- [src/Seller/Application/Command/CreateSeller/CreateSellerCommand.php](https://github.com/erinlessard/example-without-laravel/blob/main/src/Seller/Application/Command/CreateSeller/CreateSellerCommand.php)
  - Could easily be dispatched in a queue job (Command Pattern or CQRS)

- [src/Seller/Domain/Seller.php](https://github.com/erinlessard/example-without-laravel/blob/main/src/Seller/Domain/Seller.php)
    - Private constructor forcing creation via static factory method.
    - Used `Brick\Money` for `Money` Value Object. It includes currency code, supporting GBP and opening the door for any other currencies in the future. The `Brick` packages are pretty battle tested and I'd be comfortable using them in a Domain object.
    - Used the "minor" amount for the amount of `Money`. This is the cent value of the amount (eg $100.00 = 10000), which can be stored in a database column as an integer and not float, avoiding any potential rounding issues.
    - Public `hydrate` method, let's pretend that's not there because there's no ORM doing hydration here :)

- [src/Seller/Infrastructure/Api/Cli.php](https://github.com/erinlessard/example-without-laravel/blob/main/src/Seller/Infrastructure/Api/Cli.php)
  - `php .\src\Seller\Infrastructure\Api\Cli.php --name "test name" --product digital --payout 250000`
  - Added quick and dirty CLI use case of Application layer. Following hexagonal architectural practices, a controller is simply one way to access the application layer. Splitting the Application layer up allows it to be invoked in different ways, while still working consistently.

## Test Notes

- [tests/Seller/Infrastructure/ApiControllerTest.php](https://github.com/erinlessard/example-without-laravel/blob/main/tests/Seller/Infrastructure/ApiControllerTest.php)
    - Had to create request objects and pass them into Slim to test the controller. Not very elegant.