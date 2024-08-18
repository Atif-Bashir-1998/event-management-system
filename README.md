## Event Management System

#### Setup
- `git clone <REPOSITORY_URL>`
- `cd /event-management-system`
- `composer install`
- `npm install`
- `php artisan migrate:fresh --seed`
- Terminal 1: `php artisan serve`
- Terminal 2: `npm run dev`

#### **Client Requirement Overview:**
You, as a client, require a comprehensive Event Management System (EMS) that will enable different types of users—Admins, Organizers, and Attendees—to interact with the platform to create, manage, and participate in events. The system needs to cater to a variety of event types, including conferences, workshops, and meetups, ensuring a seamless experience for all users. Below, I will detail each aspect of the project, breaking down the requirements, scenarios, and expectations for the EMS.

#### **Key Features & Detailed Requirements:**

1. **User Roles:**
   - **Admin Role:**
     - **Access Control:** Admins will have the highest level of access, allowing them to manage all aspects of the system, including user management, event creation, and report generation.
     - **Complex Scenario:** Admins should be able to assign roles to users dynamically. For instance, if an attendee wants to organize an event, the admin can upgrade their role to Organizer. Similarly, admins can demote an organizer if necessary.
     - **Example:** An Admin notices a high demand for a particular workshop. They may need to promote additional users to Organizers to manage the influx of attendees.
     
   - **Organizer Role:**
     - **Access Control:** Organizers can create and manage their events, handle ticket sales, and communicate with attendees.
     - **Complex Scenario:** Organizers should be able to handle multi-day events, set various ticket tiers (e.g., early bird, VIP), and manage different pricing for different segments of the event (e.g., main conference, after-party).
     - **Example:** An Organizer creates a two-day conference with different ticket prices for each day. They need to manage attendee lists, ensuring those who purchased VIP tickets get additional perks.

   - **Attendee Role:**
     - **Access Control:** Attendees can browse events, register, and purchase tickets. They should also receive notifications and reminders.
     - **Complex Scenario:** Attendees may want to register for multiple events simultaneously, manage their tickets, and receive refunds if an event is canceled.
     - **Example:** An attendee registers for a workshop but later realizes it conflicts with another event they’re attending. They request a refund and register for a different event.

2. **Event Creation & Management:**
   - **CRUD Operations:** Admins and Organizers should have full control over creating, reading, updating, and deleting events.
   - **Complex Scenario:** Events should support features like waiting lists, capacity limits, and automated ticket upgrades if an attendee cancels.
   - **Example:** An Organizer sets a capacity limit of 100 attendees for a workshop. As tickets sell out, a waiting list is automatically created. If someone cancels, the first person on the waiting list is automatically registered and notified.

3. **User Registration & Tickets:**
   - **Registration Process:** The system should support a smooth registration process where attendees can select events, choose ticket types, and complete payments.
   - **Complex Scenario:** Implement a referral system where attendees can invite friends to register, and both receive discounts.
   - **Example:** An attendee invites a friend using a unique referral code. Both the referrer and the referred receive a 10% discount on their tickets.

4. **Notifications:**
   - **Email & SMS Notifications:** The system should send notifications for various triggers such as event reminders, updates, and cancellations.
   - **Complex Scenario:** Notifications should be customizable by the admin/organizer to include personalized messages or additional instructions.
   - **Example:** An Organizer wants to send a personalized thank-you email to all attendees after the event, along with a link to a feedback form.

5. **Event Reminders:**
   - **Laravel Jobs:** Utilize Laravel Jobs to automate the sending of reminders one day before the event.
   - **Complex Scenario:** Implement a queue system to handle large volumes of reminder notifications efficiently, avoiding delays or server overload.
   - **Example:** A large conference has 1000 attendees. The system queues and sends reminders in batches to ensure timely delivery without overloading the server.

6. **Reports & Analytics:**
   - **Report Generation:** Admins should be able to generate reports on event attendance, user engagement, ticket sales, and revenue.
   - **Complex Scenario:** Provide graphical analytics showing trends over time, such as increasing/decreasing attendance rates, popular event types, or peak registration times.
   - **Example:** An Admin reviews a report showing that workshops on weekends attract more attendees than those on weekdays. This insight helps them schedule future events more effectively.

7. **Event Cancellation:**
   - **Cancellation Process:** Implement functionality to allow Organizers or Admins to cancel events, triggering automatic refunds and notifications.
   - **Complex Scenario:** Handle partial refunds for events that span multiple days, where only some days are canceled.
   - **Example:** An Organizer cancels the second day of a two-day conference. The system needs to process refunds for just the second day, adjusting attendee records and sending notifications accordingly.

#### **Testing Focus & Scenarios:**

1. **Unit and Feature Tests:**
   - **Event Creation:** Test scenarios where events are created with different parameters (e.g., single-day vs. multi-day, different ticket tiers).
   - **Registration Process:** Ensure that the registration process works under various conditions, such as simultaneous registrations, discount codes, and edge cases like capacity limits.
   - **Notification Processes:** Verify that notifications are sent correctly for each trigger, such as reminders, updates, and cancellations.

2. **Testing Laravel Jobs:**
   - **Reminder Delivery:** Test the job queue system for sending reminders, ensuring that it handles large volumes efficiently.
   - **Edge Case:** Test scenarios where reminders need to be sent across different time zones or to large numbers of international attendees.

3. **Testing Event-Driven Architecture:**
   - **Laravel Events & Listeners:** Implement and test event-driven functionality where actions trigger specific listeners. For example, registering for an event could trigger an email notification, ticket generation, and an analytics update.
   - **Edge Case:** Test complex scenarios where multiple events trigger listeners simultaneously, ensuring there are no conflicts or missed actions.

### **Project Deliverables:**
1. Fully functional Event Management System.
2. Comprehensive test suite covering all major functionalities.
3. Detailed documentation for the system architecture, user roles, and test cases.
4. User manual for Admins, Organizers, and Attendees.

This detailed project plan should help you practice and enhance your Laravel knowledge and testing skills while building a practical and scalable Event Management System.
