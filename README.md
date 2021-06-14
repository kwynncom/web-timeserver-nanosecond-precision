# web-timeserver-nanosecond-precision
nanosecond precise web timeserver with chrony validation

https://kwynn.com/t/20/10/timeserver/ - live at

The above explains quite a bit (it is this repo's /index.html).  The following was written months later and is esoteric.


My sourcestats parser makes several assumptions / has limitations:

1. I'm assuming a single source
2. I have no idea if "h" for hours will ever happen.  My observation is that it would be rare, so I can't test for it.

sourcestats example (keyword-ssExOut):

$ chronyc sourcestats
210 Number of sources = 1
Name/IP Address            NP  NR  Span  Frequency  Freq Skew  Offset  Std Dev
==============================================================================
169.254.169.123             6   5   86m     -0.007      0.045  -5409ns    20us

my output, with discussion

1. Number of timeserver polls / samples currently used in the timekeeping mathematical model (regression).
2. Span, in seconds, that these polls were taken.  

With a stable model, the span may go over an hour.  Right now I'm seeing 86m (minutes) on kwynn.com, as above.  A few seconds means that chrony has just 
restarted.  

Chrony doesn't show data until 3 points / polls / samples. How high the number gets depends on minpoll and maxpoll settings.   The short answer is that 
a sample of 6 may be as perfect as it gets.  I didn't set minpoll and maxpoll on AWS (kwynn.com) in large part because Amazon's example doesn's show this.  
Amazon's example is 

server 169.254.169.123 prefer iburst
https://aws.amazon.com/blogs/aws/keeping-time-with-amazon-time-sync-service/

I added xleave after iburst, but I'm not sure it does anything.  I think xleave does (sometimes) work between my local system and kwynn.com.  

Minpoll and maxpoll refer to the min and max polling interval.  I don't remember what the default maxpoll is, but kwynn.com is often at a poll 
interval of 1,000 seconds (17 minutes), so that 86m span is 6 samples (86 / 17 = 5 and then + 1 for the 0th point).  At that setting, kwynn.com is 
extremely accurate (with "extreme" perhaps to be defined later).  I am not trying to achieve anything better than kwynn.com.

On my local server, I have the maxpoll interval set to 6 / 2^6s / 64s.  My local machine keeps 64 samples when it's stable, so that that's a span of 63 
minutes (currently showing 66), which is on the order of Kwynn.com.  
