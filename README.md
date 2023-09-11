# Server Log Accessor

This project is a simple REST API that consolidates access to multiple log files on a server. It offers a range of functionalities, making it a versatile tool for log management:

**Key Features:**

1. **Filtering:** Easily filter log entries using simple search queries.
2. **Pagination:** Retrieve log entries in sets of 10, 25, 50, or 100, spanning across all log files.
3. **Date and Time Filtering:** Filter logs based on date and time ranges.
4. **Regular Expression Filtering:** Apply regular expressions to filter log entries according to your specific criteria.
5. **Multiple Filters:** Support for applying multiple filters simultaneously.

**Requirements:**

- Log files, preferably in the Common Log Format (CLF), are stored in a directory on the server.
- The project uses PHP 8.2 and Symfony 6.2.
- MySQL serves as a caching layer.
- Docker containers streamline the deployment process.

**Project Components:**

1. **REST API:** This component reads logs from a MySQL database, acting as a cache layer. It allows users to access log data with ease. Notably, the API supports applying the same filter multiple times in a single request.

2. **Collector Service:** The asynchronous backend includes a Collector service. This service scans log files in the configured directory on the server. It can skip non-log files and those not matching the CLF format. You can set conditions to stop parsing a file if a certain number of log lines don't match the format. The Collector service batches data to be flushed periodically, enhancing efficiency.

**Installation:**

- Clone the repository.
- Navigate to the project directory.
- Install Composer if you haven't already (`curl -sS https://getcomposer.org/installer | php`).
- Run `php composer.phar install` to install project dependencies.
- Build Docker containers using `docker-compose up --build`.
- Configure the database connection in `.env`.
- Create the database with `php bin/console doctrine:database:create`.
- Run migrations with `php bin/console doctrine:migration:migrate`.

**Example Log Entry (CLF):**

Here's an example of a log entry in the Common Log Format:

```
127.0.0.1 - - [10/Sep/2023:23:30:35 +0200] "GET /favicon.ico HTTP/1.1" 404 183
```

**Usage and API Examples:**

- To start collecting logs, run the following console command: `app/console logs-grabber [logDir] [--keepMax=1 day]`.

- The API offers various filtering options, such as filtering logs containing "localhost" and "example":

  ```
  GET /api/v1/logs?textLike[]=localhost&textLike[]=example
  ```

- You can filter logs between two dates:

  ```
  GET /api/v1/logs?datetimeBetween[]=2023-01-01 00:00:00,2023-02-01 12:00:00
  ```

- Apply multiple date ranges or other filters in a single request:

  ```
  GET /api/v1/logs?datetimeBetween[]=2023-01-01 00:00:00,2021-02-01 12:00:00&datetimeBetween[]=2023-03-01 00:00:00,2023-04-01 12:00:00
  ```

- Limit the number of log entries:

  ```
  GET /api/v1/logs?limit=50
  ```

- Paginate through log results:

  ```
  GET /api/v1/logs?page=1
  ```

- Filter logs using regular expressions:

  ```
  GET /api/v1/logs?textRegex[]=[a-z]{2}&textRegex[]=[0-9]+
  ```

Feel free to ask if you have any further questions or need additional assistance with my project!

Note: vendor library LogParser has a deprecation warnings, it is expected for demonstration purposes.

They are marked as deprecated during test run as well.

Not implemented, not finished due to lack of time:
- full test coverage;
- authorization part.
