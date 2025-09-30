## News Aggregator API

This project is a backend service for a news aggregator. It fetches articles from multiple external news APIs (e.g., NewsAPI, The Guardian, New York Times) and stores them in a local database. The backend exposes API endpoints for retrieving articles with filtering and search capabilities.

The system uses queues and the Laravel scheduler to keep the local database updated with the latest news.

## 🚀 Features
- Fetch and store articles from multiple sources (NewsAPI, The Guardian, & NYTimes).
- API for retrieving articles with filtering by: source, category, date range, and keyword search.
- User preference support (preferred categories, sources, authors).
- Uses queue workers for async fetching.
-Uses scheduler (cron) to update the database with fresh articles.

## API Documentation
postman collection: [News Aggregator API Documentation.](./postman_collection.json)

## License

News Aggregator is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
