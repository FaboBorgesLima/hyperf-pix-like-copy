# Hyperf Pix Like Clone

This is a clone-like of the Pix payment system using the Hyperf framework and microservices architecture.

## Services

- **Auth Service**: Handles user authentication and management.
- **Transaction Service**: Manages transactions and payment processing. (under development)
- **Notification Service**: Responsible for sending notifications to users. (under development)
- **RabbitMQ**: Used for message queuing between services.

Every service is built using the Hyperf framework and runs in its own Docker container. The services communicate with each other using RabbitMQ for asynchronous messaging.

All services have their own Docker Compose configuration with their own Databases, and the main `docker-compose.yaml` file includes these configurations to orchestrate the entire application.

## Setup

Run the following command to set up the application:

```bash
./setup.sh
```

```bash
docker compose up
```
