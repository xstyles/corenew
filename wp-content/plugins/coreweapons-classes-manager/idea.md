Shortcodes required:

## Schedule a class widget

- **Visible to:** Teachers

- **Purpose:** Schedule a new class meeting for students to join.

- **Key points:**

  - A class should be for a specific available course/subject.

  - The available courses/subjects are related (1:1) to WooCommerce products which are open to customer 
    purchases.

  - The teacher gets to set starting date & time and duration for each class.

  - The duration is usually fixed to a minimum of 1hour. 
    Choice of duration must be in increments of 30 mins each. For example: 1 hour, 1.5 hours, 2 hours ... etc.

  - The date/time for the class must be always set in the future.  
    (*Implicit validation required*: Only allow future date/time to be selected from the UI controls)

  - When the form is submitted, the server must also check whether the date/time is in the future.
    If not, then it must invalidate and return the form as failed.

## Attend a class widget

- **Visible to:** Students

- **Purpose:** Allow a student to attend a class previously scheduled by a teacher.

- **Key points:**

  - The classes visible to a student depends on the **latest** WooCommerce product order related to the student.  
    **TODO:** How to query the database for fetching the specific course/subjects the logged-in student 
    should have access to?
    
  - The student can click on a class to open a Google Meet session for attending the class
  
## Attendance status widget

- **Visible to:** Parents, Teachers

- **Purpose:** Allow a parent/teacher to check which students have attended which classes

- **Key points:**
  
  - If the current user is a parent (`member_type === 'parent'`), then they must only be able to see their
    own wards' attendance status.  
    We have to check with all related purchases of a parent to figure which student profiles were created when 
    they purchased the course product/subscription/membership.
    
  - If the current user is a teacher (`member_type === 'teacher'`), then they must be able to see 
    all students' attendance status.

  - Attendance data must be shown in descending order of creation
