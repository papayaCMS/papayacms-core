@echo off
FOR /f "delims=" %%A in ('dir . /AD /s /b') do (
  svgo -f %%A --disable=convertShapeToPath --pretty --indent=1
)
