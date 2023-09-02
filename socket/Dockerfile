FROM node:16-slim

RUN apt-get update -y --fix-missing \
    && apt-get upgrade -y \
    && apt-get install -y git

WORKDIR /app
RUN adduser --system www && chown -R www /app
USER www

# copy this first to not reinstall everything on a random file change
COPY socket/package.json ./
RUN npm install

# put the installed bin into PATH
RUN echo 'export PATH=$PATH:$(npm bin)' >> ~/.profile
SHELL ["/bin/sh", "-cl"]
COPY ./socket ./

# here we can pass args during build stage
ARG ENVIRONMENT=production
# and use the ARG as an env var
ENV ENV=$ENVIRONMENT

CMD npm run start

USER root
ENV SOCKETCLUSTER_PORT=8000
ENV SOCKETCLUSTER_WORKERS=5
ENV SOCKETCLUSTER_BROKERS=5