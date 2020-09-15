select region, count(*), (select count(*) from gouv_local_orga)
from gouv_local_orga_detail
where process_id = 304
group by region;

select count(*)
from gouv_local_orga_detail
where process_id = 304;

select region, count(*) * 100.0 / (select count(*) from gouv_local_orga_detail), count(*), (select count(*) from gouv_local_orga_detail)
from gouv_local_orga_detail
where process_id = 304
group by region;