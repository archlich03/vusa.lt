declare namespace App.Entities {
  export type AgendaItem = models.AgendaItem;
  export type Banner = models.Banner;
  export type Calendar = models.Calendar;
  export type Category = models.Category;
  export type ChangelogItem = models.ChangelogItem;
  export type Comment = models.Comment;
  export interface Contact extends models.Contact {
    comments: Array<models.Comment>;
  }

  export interface Doing extends Omit<models.Doing, "state"> {
    state:
      | "draft"
      | "pending_changes"
      | "pending_padalinys_approval"
      | "pending_final_approval"
      | "approved"
      | "pending_completion"
      | "completed"
      | "cancelled";
  }
  export type Dutiable = models.Dutiable;

  export type Duty = models.Duty & {
    roles?: Array<models.Role> | null;
    roles_count?: number | null;
  }

  export type Goal = models.Goal;
  export type GoalGroup = models.GoalGroup;
  export type GoalMatter = models.GoalMatter;
  export type Institution = models.Institution;
  export type MainPage = models.MainPage;
  export type Matter = models.Matter;
  // export interface Media extends models.Media {
  //   original_url?: string;
  // }

  export type Meeting = models.Meeting;

  export type Navigation = models.Navigation;
  export type News = models.News;

  export interface Notification<T> {
    created_at: string;
    data: T;
    id: string;
    notifiable_id: string;
    notifiable_type: string;
    read_at: string | null;
    type: string;
    updated_at: string;
  }

  export type Padalinys = models.Padalinys;
  export type Page = models.Page;
  export type Permission = models.Permission;
  export type Registration = models.Registration;
  export type RegistrationForm = models.RegistrationForm;
  export type Relationship = models.Relationship;
  export type Relationshipable = models.Relationshipable;
  export type Reservation =
    Omit<models.Reservation, "resources" | "users"> & {
    comments?: Array<models.Comment> | null;
    resources?: Array<App.Entities.Resource> | null;
    users?: Array<App.Entities.User> | null;
  }
  export type Resource = Omit<models.Resource, "is_reservable"> & {
    is_reservable: 0 | 1;
    pivot?: App.Entities.ReservationResource | null;
    media?: App.Entities.Media[] | null;
  }
  export type ReservationResource =
    Omit<models.ReservationResource, "state"> & {
    state:
      | "created"
      | "reserved"
      | "lent"
      | "returned"
      | "rejected"
      | "cancelled";
    comments?: Array<models.Comment> | [];
  }
  export type Role = models.Role;
  export type SaziningaiExam = models.SaziningaiExam;
  export type SaziningaiExamFlow = models.SaziningaiExamFlow;
  export type SaziningaiExamObserver = models.SaziningaiExamObserver;
  export type SharepointFile = models.SharepointFile;
  export type SharepointFileable = models.SharepointFileable;
  export type Tag = models.Tag;
  export type Task = models.Task;
  export type Type = models.Type;

  export type User =
    Omit<models.User, "padaliniai" | "reservations"> & {
    padaliniai?: Array<models.Padalinys> | null;
    reservations?: Array<App.Entities.Reservation> | null;
    roles?: Array<models.Role> | null;
    roles_count?: number | null;
    pivot?: models.Dutiable | null;
  }
}
