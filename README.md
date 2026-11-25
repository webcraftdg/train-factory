# TrainFactory

TrainFactory is a web application designed to help sports instructors
create, organize, manage and export structured training sessions.

Initially developed for Savate French Boxing instructors, the application
is designed to remain flexible and adaptable to other sports disciplines.

## Overview

Preparing a training session requires more than defining a list of
exercises. Instructors need to organize activities, define technical
instructions, identify pedagogical objectives and manage training time.

TrainFactory provides a dedicated interface that allows instructors to
build their training sessions directly in a layout inspired by their
final training documents.

The application aims to simplify session preparation, encourage exercise
reuse and reduce the time spent creating training documents.

## Features

- Create, edit, duplicate and manage training sessions.
- Organize sessions into reusable training blocks.
- Define activities with technical instructions and pedagogical objectives.
- Create exercises or select existing exercises from a shared library.
- Associate up to two exercises with each activity.
- Manage activity duration and hydration breaks.
- Export training sessions to Excel.
- Responsive interface for desktop, tablet and mobile devices.
- User authentication and access management.

## Training Session Structure

A training session is organized into three main levels:

```text
Training Session
├── Training Block
│   ├── Activity
│   │   ├── Exercise A
│   │   ├── Exercise B
│   │   ├── Technical instructions
│   │   ├── Pedagogical objectives
│   │   ├── Duration
│   │   └── Hydration break
│   └── Activity
└── Training Block
```

By default, a training session contains three blocks:

- Warm-up
- Main training
- Cool-down

Exercises are stored independently and can be reused across multiple
activities and training sessions.

## Technical Stack

### Backend

- PHP
- Symfony
- Doctrine ORM
- REST API
- MySQL / MariaDB
- JWT authentication
- Composer

### Frontend

- Angular
- TypeScript
- Angular Signals
- Angular Reactive Forms
- Tailwind CSS

### Data Export

TrainFactory uses the WebCraftDG DataPipeline library to manage
document generation and export processing.

DataPipeline provides the processing infrastructure, while dedicated
exporters handle the specific structure and formatting of training
documents.

This approach separates business data from document generation logic
and makes it easier to support additional export formats.

## Installation

### Prerequisites

- PHP compatible with the project's Composer requirements
- Composer
- Node.js
- npm
- MySQL or MariaDB
- Symfony CLI (optional)

### 1. Clone the repository

```bash
git clone https://github.com/WebCraftDG/train-factory.git
cd train-factory
```

### 2. Install backend dependencies

```bash
composer install
```

### 3. Configure the environment

Create a `.env.local` file and configure the database connection:

```dotenv
APP_ENV=dev
APP_SECRET=your_application_secret

DATABASE_URL="mysql://user:password@127.0.0.1:3306/trainfactory"
```

Configure the authentication environment variables according to the
project's security configuration.

### 4. Initialize the database

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

If development fixtures are available, they can be loaded using:

```bash
php bin/console doctrine:fixtures:load
```

Warning: loading fixtures may delete existing database data.

### 5. Install frontend dependencies

From the Angular application directory:

```bash
npm ci
```

### 6. Start the application

Start the Symfony development server:

```bash
symfony server:start
```

From the Angular application directory, start the frontend:

```bash
npm start
```

Make sure the Angular development server is configured to communicate
with the Symfony REST API.

## Production Build

From the Angular application directory:

```bash
npm ci
npm run build -- --configuration production
```

Copy the generated browser application files into the Symfony
`public/angular/` directory.

The production web server must expose the Symfony `public/` directory
as its document root.

Install the production PHP dependencies:

```bash
composer install --no-dev --optimize-autoloader
```

Apply database migrations and clear the production cache:

```bash
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
php bin/console cache:clear --env=prod
```

## Usage

### Create a training session

1. Log in to the application.
2. Open the training sessions page.
3. Create a new training session.
4. Define its name, theme and target audience.
5. Organize the training blocks.
6. Add activities to each block.
7. Create exercises or select them from the exercise library.
8. Define instructions, objectives and durations.
9. Save the training session.

### Reuse existing exercises

The exercise library allows instructors to reuse existing exercises
without having to rewrite their descriptions for each training session.

### Duplicate a training session

An existing training session can be duplicated and adapted to a new
training objective or audience.

### Export a training session

Saved training sessions can be exported to Excel, providing a structured
document that can be printed or used during training.

## Roadmap

- PDF export.
- Further improvements to the mobile user experience.
- Improved exercise library management.
- Additional training session templates.
- Automated tests.

## Project Philosophy

TrainFactory follows a business-oriented approach.

The application is designed around the actual workflow of sports
instructors rather than around independent technical CRUD interfaces.

The objective is to provide a simple, intuitive and maintainable
application that focuses on the user's needs while keeping technical
complexity behind the scenes.

## Author

Developed by David Ghyse — WebCraftDG.

## Related Projects

- [WebCraftDG DataPipeline](https://github.com/WebCraftDG/data-pipeline)
