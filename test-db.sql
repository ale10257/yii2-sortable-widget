DROP TABLE IF EXISTS public.sort CASCADE;

CREATE TABLE public.sort
(
    id        integer NOT NULL,
    parent_id integer,
    sort      integer
);

ALTER TABLE public.sort
    ADD CONSTRAINT sort_pkey PRIMARY KEY (id);

ALTER TABLE public.sort
    ADD CONSTRAINT fk_sort_parent_id FOREIGN KEY (parent_id) REFERENCES public.sort(id) ON UPDATE CASCADE ON DELETE CASCADE;