# Legacy PHP CMS → MVC Migration

A PHP/MySQL CMS being incrementally refactored from a procedural codebase into a more maintainable MVC architecture.

This project started as a traditional PHP CMS/blog application and has evolved into a practical exploration of **object-oriented PHP, MVC architecture, database abstraction, authentication, security and software design**.

Rather than rewriting the application from scratch, I am progressively replacing tightly coupled components while documenting the architectural decisions and lessons learned along the way.

---

## 🎯 Project Goals

The main goals of the project are to:

* Understand MVC architecture through practical implementation
* Separate business logic from presentation
* Improve maintainability and code organisation
* Replace tightly coupled database access with cleaner abstractions
* Introduce dependency injection
* Improve authentication and security
* Develop better testing and development practices
* Gain experience refactoring an existing codebase

---

## 🏗️ Architecture

The application is being progressively migrated towards a structured MVC architecture.

Key concepts explored include:

* **Model-View-Controller (MVC)**
* Front Controller pattern
* Routing
* Controllers
* Services
* Repository pattern
* Dependency Injection
* Object-Oriented PHP
* Namespaces
* PSR-4 autoloading
* Composer

The migration is intentionally incremental so that the differences between the original procedural implementation and the newer architecture can be understood and evaluated.

---

## 🔐 Security

Security is an important part of the migration.

Areas explored include:

* Password hashing
* Session management
* Authentication and authorisation
* Role-based access control
* CSRF protection
* Input validation
* Output escaping
* SQL injection prevention
* Secure database access using prepared statements

Additional security improvements are planned as the application continues to evolve.

---

## 🗄️ Database

The original application used `mysqli` and contained database queries directly within page-level code.

The migration is progressively moving towards:

* PDO
* Prepared statements
* Repository-based database access
* Separation of database logic from application logic
* Improved configuration management

Future work includes database migrations and seeders.

---

## 🔄 Migration Approach

The project follows an incremental refactoring process:

1. Understand the existing implementation
2. Identify architectural or maintenance problems
3. Design a cleaner approach
4. Implement the new structure
5. Test the changes
6. Compare the new implementation with the legacy approach
7. Repeat

This approach has allowed me to work with an existing codebase rather than only building applications from scratch.

---

## 💡 What I've Learned

One of the most valuable parts of this project has been learning that software architecture becomes increasingly important as an application grows.

Through the migration I've developed a stronger understanding of:

* Separation of concerns
* Dependency injection
* Repository and service patterns
* Database abstraction
* Authentication and authorisation
* Secure database access
* Refactoring legacy code
* Organising larger PHP applications
* Making code easier to maintain and extend

I've also learned that good architecture is not about creating unnecessary complexity — it is about making an application easier to understand, change and maintain.

---

## 🛠️ Technologies

**Backend**

* PHP
* Object-Oriented PHP
* MVC
* PDO
* MySQL

**Development**

* Composer
* Git
* GitHub
* PSR-4 autoloading

**Web**

* HTML
* CSS
* JavaScript

---

## 📁 Project Structure

The application is organised around a structured PHP application architecture, including areas such as:

```text
app/
bootstrap/
config/
public/
routes/
```

The structure continues to evolve as additional parts of the legacy application are migrated.

---

## 🚧 Current Status

This is an active refactoring project.

Some components have been migrated to the newer architecture while other legacy areas remain in place. This is intentional: the project is being developed incrementally so that architectural changes can be evaluated as they are introduced.

### Planned improvements

* Complete remaining MVC migrations
* Expand automated testing
* Improve validation
* Continue database abstraction
* Improve reusable view components
* Add development environment tooling
* Continue security improvements
* Explore REST API development

---

## 📚 Why I Built This

I wanted to understand **why** modern PHP applications use patterns such as MVC, dependency injection and repository abstractions rather than simply learning the terminology.

Starting with an existing procedural application provided an opportunity to experience the problems that these patterns are designed to solve.

The project therefore serves as both a working application and a practical record of my progression from procedural PHP towards more structured application development.

---

## License

This project is provided for educational and personal development purposes.
